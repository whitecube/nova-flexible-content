<template>
    <field-wrapper>
        <div class="py-6 px-8 w-full">
            <div class="mb-6" v-if="fieldLabel">
                <form-label :for="field.attribute" :class="{
                      'mb-2': field.helpText && showHelpText
                  }">
                    {{ fieldLabel }}

                    <span v-if="field.required" class="text-danger text-sm">{{
                        __('*')
                    }}</span>

                    <span>
                        <button
                            dusk="expand-group"
                            type="button"
                            class="shrink-0 group-control btn border border-gray-200 dark:border-gray-700 w-8 h-8 block"
                            :title="__('Expand')"
                            @click.prevent="expand"
                            v-if="collapsed">
                            <icon type="plus" class="align-top" width="16" height="16" />
                        </button>
                        <button
                            dusk="collapse-group"
                            type="button"
                            class="group-control btn border border-gray-200 dark:border-gray-700 w-8 h-8 block"
                            :title="__('Collapse')"
                            @click.prevent="collapse"
                            v-else>
                            <icon type="minus" class="align-top" width="16" height="16" />
                        </button>
                    </span>
                </form-label>

                <help-text v-if="showHelpText">
                    {{ field.helpText }}
                </help-text>
            </div>

            <slot name="field" v-bind:collapsed="collapsed" />

            <help-text
                class="error-text mt-2 text-danger"
                v-if="showErrors && hasError"
            >
                {{ firstError }}
            </help-text>
        </div>
    </field-wrapper>
</template>

<script>
import { HandlesValidationErrors, mapProps } from 'laravel-nova'

export default {
    mixins: [HandlesValidationErrors],

    props: {
        field: { type: Object, required: true },
        fieldName: { type: String },
        showErrors: { type: Boolean, default: true },
        ...mapProps(['showHelpText']),
    },

    data() {
        return {
            collapsed: this.field.collapsed
        };
    },

    computed: {
        fieldLabel() {
            // If the field name is purposefully empty, hide the label altogether
            if (this.fieldName === '') {
                return false;
            }

            return this.fieldName || this.field.singularLabel || this.field.name
        },
    },

    methods: {
        expand() {
            this.collapsed = false;
        },

        collapse() {
            this.collapsed = true;
        },
    }
};
</script>
