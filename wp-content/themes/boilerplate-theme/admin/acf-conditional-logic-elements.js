/**
 * Adds support for conditional logic for non-field elements.
 *
 * Currently conditional logic can only be applied to elements
 * within the `.acf-label` container.
 *
 * Copied from acf-input.js at v6.0.7:
 *
 * @see {@link https://github.com/AdvancedCustomFields/acf/blob/6.0.7/assets/build/js/acf-input.js}
 *
 * @see {@link @link https://gist.github.com/mcaskill/7a10da685b3910ba103961c2eb67d8a6}
 */

(function ($, acf) {
    if (acf == null) {
        console.warn('ACF is unavailable');
        return;
    }

    // vars
    var CONTEXT = 'conditional_logic';

    new acf.Model({
        id: 'customConditionsManager',
        priority: 30, // run actions later

        actions: {
            new_field: 'onNewField'
        },

        onNewField: function (field) {
            var excludedFieldTypes = acf.applyFilters('exclude_field_types', [
                'clone',
                'flexible_content',
                'group',
                'repeater'
            ]);

            if (excludedFieldTypes.includes(field.get('type'))) {
                return;
            }

            var $elements = field.$('[data-toggle="conditional"]');

            $elements.each(function () {
                var $el = $(this);

                if ($el.data('conditions')) {
                    var model = $el.data('cira.conditions');

                    if (!model) {
                        model = new Conditions(this);

                        $el.data('cira.conditions', model);
                    }

                    model.render();
                }
            });
        }
    });

    var Conditions = acf.Model.extend({
        id: 'CustomConditions',
        $el: false, // The element with "data-conditions" (target).
        hidden: false,

        data: {
            timeStamp: false, // Reference used during "change" event.
            groups: [] // The groups of condition instances.
        },

        setup: function (element) {
            this.$el = $(element);

            // vars
            var conditions = this.$el.data('conditions');

            // detect groups
            if (conditions instanceof Array) {
                // detect groups
                if (conditions[0] instanceof Array) {
                    // loop
                    conditions.map(function (rules, i) {
                        this.addRules(rules, i);
                    }, this);

                    // detect rules
                } else {
                    this.addRules(conditions);
                }

                // detect rule
            } else {
                this.addRule(conditions);
            }
        },

        change: function (event) {
            // this function may be triggered multiple times per event due to multiple condition classes
            // compare timestamp to allow only 1 trigger per event
            if (this.get('timeStamp') === event.timeStamp) {
                return false;
            } else {
                this.set('timeStamp', event.timeStamp, true);
            }

            // render condition and store result
            var changed = this.render();
        },

        render: function () {
            return this.calculate() ? this.show() : this.hide();
        },

        show: function () {
            var lockKey = this.cid;

            // show element and store result
            var changed = acf.show(this.$el, lockKey);

            // do action if visibility has changed
            if (changed) {
                this.prop('hidden', false);
                acf.doAction('show_element', this, CONTEXT);
            }

            // return
            return changed;
        },

        hide: function () {
            var lockKey = this.cid;

            // hide field and store result
            var changed = acf.hide(this.$el, lockKey);

            // do action if visibility has changed
            if (changed) {
                this.prop('hidden', true);
                acf.doAction('hide_element', this, CONTEXT);
            }

            // return
            return changed;
        },

        calculate: function () {
            // vars
            var pass = false;

            // loop
            this.getGroups().map(function (group) {
                // igrnore this group if another group passed
                if (pass) {
                    return;
                }

                // find passed
                var passed = group.filter(function (condition) {
                    return condition.calculate();
                });

                // if all conditions passed, update the global var
                if (passed.length == group.length) {
                    pass = true;
                }
            });

            return pass;
        },

        hasGroups: function () {
            return this.data.groups != null;
        },

        getGroups: function () {
            return this.data.groups;
        },

        addGroup: function () {
            var group = [];
            this.data.groups.push(group);
            return group;
        },

        hasGroup: function (i) {
            return this.data.groups[i] != null;
        },

        getGroup: function (i) {
            return this.data.groups[i];
        },

        removeGroup: function (i) {
            this.data.groups[i].delete;
            return this;
        },

        addRules: function (rules, group) {
            rules.map(function (rule) {
                this.addRule(rule, group);
            }, this);
        },

        addRule: function (rule, group) {
            // defaults
            group = group || 0;

            // vars
            var groupArray;

            // get group
            if (this.hasGroup(group)) {
                groupArray = this.getGroup(group);
            } else {
                groupArray = this.addGroup();
            }

            // instantiate
            var condition = this.newCondition(rule, this);

            // bail ealry if condition failed (element did not exist)
            if (!condition) {
                return false;
            }

            // add rule
            groupArray.push(condition);
        },

        hasRule: function () {},

        getRule: function (rule, group) {
            // defaults
            rule = rule || 0;
            group = group || 0;

            return this.data.groups[group][rule];
        },

        removeRule: function () {},

        /**
         * Create a new condition.
         *
         * Based on {@see acf.newCondition}.
         *
         * @param  {Array}      rule
         * @param  {Conditions} conditions
         * @return {acf.Condition}
         */
        newCondition: function (rule, conditions) {
            // currently setting up conditions for elementX, this element is the 'target'
            var target = conditions.$el;

            // using `acf.findFields`, instead of `acf.getField`, to exclude
            // clonable fields (skipped because `acf.findField` disables it:
            // suppressFilters) such as those in the flexible content field type.
            var field = acf
                .findFields({
                    key: rule.field,
                    limit: 1
                })
                .data('acf');

            // bail ealry if no target or no field (possible if field doesn't exist due to HTML error)
            if (!target || !field) {
                return false;
            }

            // vars
            var args = {
                rule: rule,
                target: target,
                conditions: conditions,
                field: field
            };

            // vars
            var fieldType = field.get('type');
            var operator = rule.operator;

            // get avaibale conditions
            var conditionTypes = acf.getConditionTypes({
                fieldType: fieldType,
                operator: operator
            });

            // instantiate
            var model = conditionTypes[0] || acf.Condition;

            // instantiate
            var condition = new model(args);

            // return
            return condition;
        }
    });
})(jQuery, acf);
