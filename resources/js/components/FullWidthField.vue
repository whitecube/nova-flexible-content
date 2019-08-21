<template>
    <field-wrapper>
        <div class="py-6 px-8 w-full">
            <div class="mb-6" v-if="fieldLabel">
                <form-label :for="field.attribute" :class="{
                      'mb-2': field.helpText && showHelpText
                  }">
                    {{ fieldLabel }}
                </form-label>

                <help-text :show-help-text="showHelpText">
                    {{ field.helpText }}
                </help-text>
            </div>

            <slot name="field"/>
        </div>
    </field-wrapper>
</template>

<script>
    export default {
        props: {
            field: { type: Object, required: true },
            fieldName: { type: String },
            showHelpText: { type: Boolean, default: true },
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
    };
</script>
