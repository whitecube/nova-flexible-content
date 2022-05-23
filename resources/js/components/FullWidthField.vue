<template>
  <field-wrapper>
    <div class="py-6 px-8 w-full">
      <div class="mb-6" v-if="fieldLabel">
        <form-label
          :for="field.attribute"
          :class="{
            'mb-2': field.helpText && showHelpText,
          }"
        >
          {{ fieldLabel }}

          <span v-if="field.required" class="text-danger text-sm">{{
            __("*")
          }}</span>
        </form-label>

        <HelpText
          class="help-text mt-2"
          v-if="showHelpText"
          v-html="field.helpText"
        />
      </div>

      <slot name="field" />

      <HelpText class="mt-2 help-text-error" v-if="showErrors && hasError">
        {{ firstError }}
      </HelpText>
    </div>
  </field-wrapper>
</template>

<script>
import { mapProps } from "laravel-nova";
import { HandlesValidationErrors, Errors } from "laravel-nova";

export default {
  mixins: [HandlesValidationErrors],

  props: {
    field: { type: Object, required: true },
    fieldName: { type: String },
    showErrors: { type: Boolean, default: true },
    ...mapProps(["showHelpText"]),
  },

  computed: {
    fieldLabel() {
      // If the field name is purposefully empty, hide the label altogether
      if (this.fieldName === "") {
        return false;
      }

      return this.fieldName || this.field.singularLabel || this.field.name;
    },
  },
};
</script>
