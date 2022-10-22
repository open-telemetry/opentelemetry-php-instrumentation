
#include "php.h"
#include "otel_observer.h"
#include "zend_observer.h"
#include "zend_execute.h"
#include "zend_extensions.h"
#include "zend_exceptions.h"
#include "php_otel_instrumentation.h"

static int op_array_extension = -1;

typedef struct otel_observer {
    zend_llist pre_hooks;
    zend_llist post_hooks;
} otel_observer;

static inline void func_get_this_or_called_scope(zval *zv, zend_execute_data *execute_data) {
    if (execute_data->func->op_array.scope) {
        if (execute_data->func->op_array.fn_flags & ZEND_ACC_STATIC) {
            zend_class_entry *called_scope = zend_get_called_scope(execute_data);
            ZVAL_STR(zv, called_scope->name);
        } else {
            zend_object *this = zend_get_this_object(execute_data);
            ZVAL_OBJ_COPY(zv, this);
        }
    } else {
        ZVAL_NULL(zv);
    }
}

static inline void func_get_function_name(zval *zv, zend_execute_data *ex) {
    ZVAL_STR_COPY(zv, ex->func->op_array.function_name);
}

static void func_get_args(zval *zv, zend_execute_data *ex) {
    zval *p, *q;
    uint32_t i, first_extra_arg;
    uint32_t arg_count = ZEND_CALL_NUM_ARGS(ex);

    // @see https://github.com/php/php-src/blob/php-8.1.0/Zend/zend_builtin_functions.c#L235
    if (arg_count) {
        array_init_size(zv, arg_count);
        first_extra_arg = ex->func->op_array.num_args;
        zend_hash_real_init_packed(Z_ARRVAL_P(zv));
        ZEND_HASH_FILL_PACKED(Z_ARRVAL_P(zv)) {
            i = 0;
            p = ZEND_CALL_ARG(ex, 1);
            if (arg_count > first_extra_arg) {
                while (i < first_extra_arg) {
                    q = p;
                    if (EXPECTED(Z_TYPE_INFO_P(q) != IS_UNDEF)) {
                        ZVAL_DEREF(q);
                        if (Z_OPT_REFCOUNTED_P(q)) {
                            Z_ADDREF_P(q);
                        }
                        ZEND_HASH_FILL_SET(q);
                    } else {
                        ZEND_HASH_FILL_SET_NULL();
                    }
                    ZEND_HASH_FILL_NEXT();
                    p++;
                    i++;
                }
                p = ZEND_CALL_VAR_NUM(ex, ex->func->op_array.last_var + ex->func->op_array.T);
            }
            while (i < arg_count) {
                q = p;
                if (EXPECTED(Z_TYPE_INFO_P(q) != IS_UNDEF)) {
                    ZVAL_DEREF(q);
                    if (Z_OPT_REFCOUNTED_P(q)) {
                        Z_ADDREF_P(q);
                    }
                    ZEND_HASH_FILL_SET(q);
                } else {
                    ZEND_HASH_FILL_SET_NULL();
                }
                ZEND_HASH_FILL_NEXT();
                p++;
                i++;
            }
        } ZEND_HASH_FILL_END();
        Z_ARRVAL_P(zv)->nNumOfElements = arg_count;
    }
    else {
        ZVAL_EMPTY_ARRAY(zv);
    }
}

static inline void func_get_retval(zval *zv, zval *retval) {
    if (UNEXPECTED(!retval || Z_ISUNDEF_P(retval))) {
        ZVAL_NULL(zv);
    } else {
        ZVAL_COPY(zv, retval);
    }
}

static inline void func_get_exception(zval *zv) {
    if (UNEXPECTED(EG(exception))) {
        ZVAL_OBJ_COPY(zv, EG(exception));
    } else {
        ZVAL_NULL(zv);
    }
}

static inline void func_get_declaring_scope(zval *zv, zend_execute_data *ex) {
    if (ex->func->op_array.scope) {
        ZVAL_STR_COPY(zv, ex->func->op_array.scope->name);
    } else {
        ZVAL_NULL(zv);
    }
}

static inline void func_get_filename(zval *zv, zend_execute_data *ex) {
    if (ex->func->type != ZEND_INTERNAL_FUNCTION) {
        ZVAL_STR_COPY(zv, ex->func->op_array.filename);
    } else {
        ZVAL_NULL(zv);
    }
}

static inline void func_get_lineno(zval *zv, zend_execute_data *ex) {
    if (ex->func->type != ZEND_INTERNAL_FUNCTION) {
        ZVAL_LONG(zv, ex->func->op_array.line_start);
    } else {
        ZVAL_NULL(zv);
    }
}

static void observer_begin(zend_execute_data *execute_data, zend_llist *hooks) {
    if (!zend_llist_count(hooks)) {
        return;
    }

    zval params[6];
    uint32_t param_count = 6;

    func_get_this_or_called_scope(&params[0], execute_data);
    func_get_args(&params[1], execute_data);
    func_get_declaring_scope(&params[2], execute_data);
    func_get_function_name(&params[3], execute_data);
    func_get_filename(&params[4], execute_data);
    func_get_lineno(&params[5], execute_data);

    for (zend_llist_element *element = hooks->head; element; element = element->next) {
        zend_fcall_info fci = {};
        zend_fcall_info_cache fcc = {};
        if (UNEXPECTED(zend_fcall_info_init((zval *)element->data, 0, &fci, &fcc, NULL, NULL) != SUCCESS)) {
            continue;
        }

        zval ret = { .u1.type_info = IS_UNDEF };
        fci.param_count = param_count;
        fci.params = params;
        fci.named_params = NULL;
        fci.retval = &ret;

        zend_exception_save();
        zend_object *exception = EG(prev_exception);
        EG(prev_exception) = NULL;

        if (zend_call_function(&fci, &fcc) == SUCCESS) {
            if (Z_TYPE(ret) == IS_ARRAY) {
                zend_ulong idx;
                zval *val;
                ZEND_HASH_FOREACH_NUM_KEY_VAL(Z_ARR(ret), idx, val) {
                    zval *target = NULL;
                    uint32_t arg_count = ZEND_CALL_NUM_ARGS(execute_data);
                    if (idx >= arg_count) {
                        // TODO Extend call frame?
                        // zend_vm_stack_extend_call_frame(&execute_data, arg_count, idx + 1 - arg_count);
                        for (uint32_t i = arg_count; i < idx; i++) {
                            ZVAL_UNDEF(ZEND_CALL_ARG(execute_data, i + 1));
                            ZEND_ADD_CALL_FLAG(execute_data, ZEND_CALL_MAY_HAVE_UNDEF);
                        }
                        ZEND_CALL_NUM_ARGS(execute_data) = idx + 1;
                        ZVAL_COPY(ZEND_CALL_ARG(execute_data, idx + 1), val);
                    } else {
                        target = ZEND_CALL_ARG(execute_data, idx + 1);
                        zval_dtor(target);
                        ZVAL_COPY(target, val);
                    }

                    // TODO Update params[1]
                } ZEND_HASH_FOREACH_END();
            }
        }

        zend_exception_restore();
        EG(prev_exception) = exception;
        zend_exception_restore();

        zval_dtor(&ret);
    }

    if (UNEXPECTED(ZEND_CALL_INFO(execute_data) & ZEND_CALL_MAY_HAVE_UNDEF)) {
        zend_object *exception = EG(exception);
        EG(exception) = (void*)(uintptr_t) -1;
        if (zend_handle_undef_args(execute_data) == FAILURE) {
            uint32_t arg_count = ZEND_CALL_NUM_ARGS(execute_data);
            for (uint32_t i = 0; i < arg_count; i++) {
                zval *arg = ZEND_CALL_VAR_NUM(execute_data, i);
                if (!Z_ISUNDEF_P(arg)) {
                    continue;
                }

                ZVAL_NULL(arg);
            }
        }
        EG(exception) = exception;
    }

    for (size_t i = 0; i < param_count; i++) {
        zval_dtor(&params[i]);
    }
}

static void observer_end(zend_execute_data *execute_data, zval *retval, zend_llist *hooks) {
    if (!zend_llist_count(hooks)) {
        return;
    }

    zval params[8];
    uint32_t param_count = 8;

    func_get_this_or_called_scope(&params[0], execute_data);
    func_get_args(&params[1], execute_data);
    func_get_retval(&params[2], retval);
    func_get_exception(&params[3]);
    func_get_declaring_scope(&params[4], execute_data);
    func_get_function_name(&params[5], execute_data);
    func_get_filename(&params[6], execute_data);
    func_get_lineno(&params[7], execute_data);

    for (zend_llist_element *element = hooks->tail; element; element = element->prev) {
        zend_fcall_info fci = {};
        zend_fcall_info_cache fcc = {};
        if (UNEXPECTED(zend_fcall_info_init((zval *)element->data, 0, &fci, &fcc, NULL, NULL) != SUCCESS)) {
            continue;
        }

        zval ret = { .u1.type_info = IS_UNDEF };
        fci.param_count = param_count;
        fci.params = params;
        fci.named_params = NULL;
        fci.retval = &ret;

        zend_exception_save();
        zend_object *exception = EG(prev_exception);
        EG(prev_exception) = NULL;

        if (zend_call_function(&fci, &fcc) == SUCCESS) {
            if (!Z_ISUNDEF(ret)
                && (fcc.function_handler->op_array.fn_flags & ZEND_ACC_HAS_RETURN_TYPE)
                && !(ZEND_TYPE_PURE_MASK(fcc.function_handler->common.arg_info[-1].type) & MAY_BE_VOID)) {
                if (execute_data->return_value) {
                    zval_ptr_dtor(execute_data->return_value);
                    ZVAL_COPY(execute_data->return_value, &ret);
                    // TODO Update params[2]
                }
            }
        }

        // TODO Update params[3]
        zend_exception_restore();
        EG(prev_exception) = exception;
        zend_exception_restore();

        zval_dtor(&ret);
    }

    for (size_t i = 0; i < param_count; i++) {
        zval_dtor(&params[i]);
    }
}

static void observer_begin_handler(zend_execute_data *execute_data) {
    otel_observer *observer = ZEND_OP_ARRAY_EXTENSION(&execute_data->func->op_array, op_array_extension);
    if (!observer || !zend_llist_count(&observer->pre_hooks)) {
        return;
    }

    observer_begin(execute_data, &observer->pre_hooks);
}

static void observer_end_handler(zend_execute_data *execute_data, zval *retval) {
    otel_observer *observer = ZEND_OP_ARRAY_EXTENSION(&execute_data->func->op_array, op_array_extension);
    if (!observer || !zend_llist_count(&observer->post_hooks)) {
        return;
    }

    observer_end(execute_data, retval, &observer->post_hooks);
}

static void free_observer(otel_observer *observer) {
    zend_llist_destroy(&observer->pre_hooks);
    zend_llist_destroy(&observer->post_hooks);
    efree(observer);
}

static otel_observer* create_observer(void) {
    otel_observer *observer = emalloc(sizeof(otel_observer));
    zend_llist_init(&observer->pre_hooks, sizeof(zval), (llist_dtor_func_t)zval_ptr_dtor, 0);
    zend_llist_init(&observer->post_hooks, sizeof(zval), (llist_dtor_func_t)zval_ptr_dtor, 0);

    return observer;
}

static void find_observers(HashTable *ht, zend_string *n, zend_llist *pre_hooks, zend_llist *post_hooks) {
    otel_observer *observer = zend_hash_find_ptr_lc(ht, n);
    if (observer) {
        for (zend_llist_element *element = observer->pre_hooks.head; element; element = element->next) {
            zval_add_ref((zval*)&element->data);
            zend_llist_add_element(pre_hooks, &element->data);
        }
        for (zend_llist_element *element = observer->post_hooks.head; element; element = element->next) {
            zval_add_ref((zval*)&element->data);
            zend_llist_add_element(post_hooks, &element->data);
        }
    }
}

static void find_class_observers(HashTable *ht, zend_class_entry *ce, zend_llist *pre_hooks, zend_llist *post_hooks) {
    for (; ce; ce = ce->parent) {
        find_observers(ht, ce->name, pre_hooks, post_hooks);
        // TODO Prevent traversing interfaces multiple times
        for (uint32_t i = 0; i < ce->num_interfaces; i++) {
            find_class_observers(ht, ce->interfaces[i], pre_hooks, post_hooks);
        }
    }
}

static void find_method_observers(HashTable *ht, zend_class_entry *ce, zend_string *fn, zend_llist *pre_hooks, zend_llist *post_hooks) {
    HashTable *lookup = zend_hash_find_ptr_lc(ht, fn);
    if (lookup) {
        find_class_observers(lookup, ce, pre_hooks, post_hooks);
    }
}

static otel_observer *resolve_observer(zend_execute_data *execute_data) {
    zend_function *fbc = execute_data->func;
    if (!fbc->common.function_name) {
        return NULL;
    }

    // TODO Allocate on stack
    otel_observer *observer = create_observer();

    if (fbc->op_array.scope) {
        find_method_observers(OTEL_G(observer_class_lookup), fbc->op_array.scope, fbc->common.function_name, &observer->pre_hooks, &observer->post_hooks);
    } else {
        find_observers(OTEL_G(observer_function_lookup), fbc->common.function_name, &observer->pre_hooks, &observer->post_hooks);
    }

    if (!zend_llist_count(&observer->pre_hooks) && !zend_llist_count(&observer->post_hooks)) {
        free_observer(observer);
        return NULL;
    }

    zend_hash_next_index_insert_ptr(OTEL_G(observer_aggregates), observer);

    return observer;
}

static zend_observer_fcall_handlers observer_fcall_init(zend_execute_data *execute_data) {
    if (op_array_extension == -1 || execute_data->func->common.type != ZEND_USER_FUNCTION) {
        return (zend_observer_fcall_handlers){NULL, NULL};
    }

    otel_observer *observer = resolve_observer(execute_data);
    if (!observer) {
        return (zend_observer_fcall_handlers){NULL, NULL};
    }

    ZEND_OP_ARRAY_EXTENSION(&execute_data->func->op_array, op_array_extension) = observer;
    return (zend_observer_fcall_handlers){
        zend_llist_count(&observer->pre_hooks)
            ? observer_begin_handler
            : NULL,
        zend_llist_count(&observer->post_hooks)
            ? observer_end_handler
            : NULL,
    };
}

static void destroy_observer_lookup(zval *zv) {
    free_observer(Z_PTR_P(zv));
}

static void destroy_observer_class_lookup(zval *zv) {
    HashTable *table = Z_PTR_P(zv);
    zend_hash_destroy(table);
    FREE_HASHTABLE(table);
}

static void add_function_observer(HashTable *ht, zend_string *fn, zval *pre_hook, zval *post_hook) {
    zend_string *lc = zend_string_tolower(fn);
    otel_observer *observer = zend_hash_find_ptr(ht, lc);
    if (!observer) {
        observer = create_observer();
        zend_hash_update_ptr(ht, lc, observer);
    }
    zend_string_release(lc);

    if (pre_hook) {
        zval_add_ref(pre_hook);
        zend_llist_add_element(&observer->pre_hooks, pre_hook);
    }
    if (post_hook) {
        zval_add_ref(post_hook);
        zend_llist_add_element(&observer->post_hooks, post_hook);
    }
}

static void add_method_observer(HashTable *ht, zend_string *cn, zend_string *fn, zval *pre_hook, zval *post_hook) {
    zend_string *lc = zend_string_tolower(fn);
    HashTable *function_table = zend_hash_find_ptr(ht, lc);
    if (!function_table) {
        ALLOC_HASHTABLE(function_table);
        zend_hash_init(function_table, 8, NULL, destroy_observer_lookup, 0);
        zend_hash_update_ptr(ht, lc, function_table);
    }
    zend_string_release(lc);

    add_function_observer(function_table, cn, pre_hook, post_hook);
}

bool add_observer(zend_string *cn, zend_string *fn, zval *pre_hook, zval *post_hook) {
    if (op_array_extension == -1) {
        return 0;
    }

    if (cn) {
        add_method_observer(OTEL_G(observer_class_lookup), cn, fn, pre_hook, post_hook);
    } else {
        add_function_observer(OTEL_G(observer_function_lookup), fn, pre_hook, post_hook);
    }

    return 1;
}

void observer_globals_init(void) {
    if (!OTEL_G(observer_class_lookup)) {
        ALLOC_HASHTABLE(OTEL_G(observer_class_lookup));
        zend_hash_init(OTEL_G(observer_class_lookup), 8, NULL, destroy_observer_class_lookup, 0);
    }
    if (!OTEL_G(observer_function_lookup)) {
        ALLOC_HASHTABLE(OTEL_G(observer_function_lookup));
        zend_hash_init(OTEL_G(observer_function_lookup), 8, NULL, destroy_observer_lookup, 0);
    }
    if (!OTEL_G(observer_aggregates)) {
        ALLOC_HASHTABLE(OTEL_G(observer_aggregates));
        zend_hash_init(OTEL_G(observer_aggregates), 8, NULL, destroy_observer_lookup, 0);
    }
}

void observer_globals_cleanup(void) {
    if (OTEL_G(observer_class_lookup)) {
        zend_hash_destroy(OTEL_G(observer_class_lookup));
        FREE_HASHTABLE(OTEL_G(observer_class_lookup));
        OTEL_G(observer_class_lookup) = NULL;
    }
    if (OTEL_G(observer_function_lookup)) {
        zend_hash_destroy(OTEL_G(observer_function_lookup));
        FREE_HASHTABLE(OTEL_G(observer_function_lookup));
        OTEL_G(observer_function_lookup) = NULL;
    }
    if (OTEL_G(observer_aggregates)) {
        zend_hash_destroy(OTEL_G(observer_aggregates));
        FREE_HASHTABLE(OTEL_G(observer_aggregates));
        OTEL_G(observer_aggregates) = NULL;
    }
}

void otel_instrumentation_observer_init(INIT_FUNC_ARGS) {
    if (type != MODULE_TEMPORARY) {
        zend_observer_fcall_register(observer_fcall_init);
        op_array_extension = zend_get_op_array_extension_handle("otel_instrumentation");
    }
}
