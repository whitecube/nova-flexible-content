<template>
  <div class="relative mb-4 pb-1" :id="group.key">
    <div class="w-full shrink">
      <div :class="titleStyle" v-if="group.title">
        <div
            class="leading-normal flex items-center box-content"
            :class="{
              'border-b border-gray-200 dark:border-gray-700 ': !collapsed,
              'ring-1 form-control-bordered-error': collapsed && blockHasErrors
            }">

          <button
              dusk="collapse-toggle"
              type="button"
              class="shrink-0 group-control btn border-r border-gray-200 dark:border-gray-700 w-8 h-8 block"
              :title="collapsed ? __('Expand') : __('Collapse')"
              v-tooltip="collapsed ? __('Expand') : __('Collapse')"
              @click.prevent="toggleCollapse"
          >
            <CollapseButton class="w-full" :collapsed="collapsed"/>
          </button>

          <p class="text-80 grow px-4">
            <span class="mr-3 font-semibold">#{{ index + 1 }}</span>
            {{ group.title }}
          </p>

          <div class="flex" v-if="!readonly">
            <button
                dusk="add-block"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="__('Add New Block')"
                v-tooltip="__('Add New Block')"
                ref="addBlockButton"
                @click.prevent="toggleAddBlockPopup"
            >
              <icon type="plus" class="align-top" width="16" height="16"/>
            </button>
            <button
                dusk="duplicate-block"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="__('Duplicate Block')"
                v-tooltip="__('Duplicate Block')"
                ref="duplicateBlockButton"
                @click.prevent="duplicateBlock"
            >
              <icon type="duplicate" class="align-top" width="16" height="16"/>
            </button>
            <button
                dusk="drag-group"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block nova-flexible-content-drag-button"
                :title="__('Move Element')"
                v-tooltip="__('Move Element')"
            >
              <icon type="selector" class="align-top" width="16" height="16"/>
            </button>
            <button
                dusk="hide-group"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="group.show ? __('Hide') : __('Show')"
                v-tooltip="group.show ? __('Hide') : __('Show')"
                @click.prevent="changeVisibility">
              <icon :type="group.show ? 'eye' : 'eye-off'" width="16" height="16"/>
            </button>
            <button
                dusk="move-up-group"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="__('Move Up')"
                v-tooltip="__('Move Up')"
                @click.prevent="moveUp">
              <icon type="arrow-up" class="align-top" width="16" height="16"/>
            </button>
            <button
                dusk="move-down-group"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="__('Move Down')"
                v-tooltip="__('Move Down')"
                @click.prevent="moveDown">
              <icon type="arrow-down" class="align-top" width="16" height="16"/>
            </button>
            <button
                dusk="delete-group"
                type="button"
                class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 block"
                :title="__('Delete')"
                v-tooltip="__('Delete')"
                @click.prevent="confirmRemove">
              <icon type="trash" width="16" height="16"/>
            </button>
            <delete-flexible-content-group-modal
                v-if="removeMessage"
                @confirm="remove"
                @close="removeMessage=false"
                :message="field.confirmRemoveMessage"
                :yes="field.confirmRemoveYes"
                :no="field.confirmRemoveNo"
            />
          </div>
        </div>
      </div>
      <div :class="{
        ...containerStyle,
        'ring-1 ring-rounded form-control-bordered-error': blockHasErrors
      }">
        <component
            v-for="(item, index) in group.fields"
            :key="index"
            :is="'form-' + item.component"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :field="item"
            :errors="errors"
            :mode="mode"
            :show-help-text="item.helpText != null"
            :class="{ 'remove-bottom-border': index === group.fields.length - 1 }"
        />
      </div>
      <div
          v-show="addBlockPopupVisible"
          class="rounded-lg shadow-lg max-w-xs max-h-search overflow-y-auto border border-gray-200 dark:border-gray-700 p-2 bg-white dark:bg-gray-800"
          ref="addBlockPopup"
      >
        <slot name="add-block-popup"/>
      </div>
    </div>
  </div>
</template>

<script>
import BehavesAsPanel from 'nova-mixins/BehavesAsPanel';
import Collapsable from 'nova-mixins/Collapsable'
import {CollapseButton, mapProps} from 'laravel-nova';
import {createPopper} from "@popperjs/core";

export default {
  mixins: [BehavesAsPanel, Collapsable],
  components: {CollapseButton},

  props: {
    resourceName: String,
    resourceId: {
      type: Number | String,
      required: false,
    },
    errors: {},
    group: {},
    index: {},
    field: {},
    ...mapProps(['mode'])
  },

  emits: [
    'move-up',
    'move-down',
    'remove',
    'duplicate',
    'change-visibility',
  ],

  data() {
    return {
      removeMessage: false,
      collapsed: this.group.collapsed,
      readonly: this.group.readonly,
      addBlockPopper: null,
      addBlockPopupVisible: false,
    };
  },

  mounted() {
    this.$nextTick(() => {
      this.addBlockPopper = createPopper(this.$refs.addBlockButton, this.$refs.addBlockPopup, {
        placement: 'left',
        modifiers: [{name: 'offset', options: {offset: [0, 8]}}],
      })
    })

    Nova.$on('nova-flexible-content-group-added', () => {
      this.addBlockPopupVisible = false;
    });

    Nova.$on('nova-flexible-content.add-block-clicked', () => {
      this.addBlockPopupVisible = false;
    });
  },

  computed: {
    titleStyle() {
      let classes = ['border-t', 'border-r', 'border-l', 'border-gray-200', 'dark:border-gray-700', 'rounded-t-lg'];

      if (this.collapsed) {
        classes.push('border-b rounded-b-lg');
      }

      return classes;
    },
    containerStyle() {
      return {
        'grow': true,
        'border-b': true,
        'border-r': true,
        'border-l': true,
        'border-gray-200': true,
        'dark:border-gray-700': true,
        'rounded-b-lg': true,
        'border-t rounded-tr-lg': !this.group.title,
        'hidden': this.collapsed
      }
    },

    localStorageKey() {
      const parts = [
        'nova-flexible-content'
      ]

      if (this.resourceName) {
        parts.push(this.resourceName)
      }

      if (this.resourceId) {
        parts.push(this.resourceId)
      }

      if (this.group?.key) {
        parts.push(this.group.key)
      }

      parts.push('collapsed')

      return parts.join('.');
    },
    blockHasErrors() {
      if (!this.group.key || !this.errors?.errors) {
        return false;
      }

      return Object.keys(this.errors.errors).find((key) => key.startsWith(`${this.group.key}_`)) !== undefined
    }
  },

  watch: {
    errors: {
      handler: function () {
        if (this.blockHasErrors && this.collapsed) {
          this.collapsed = false;
        }
      },
      deep: true
    }
  },

  methods: {
    moveUp() {
      this.$emit('move-up');
    },
    moveDown() {
      this.$emit('move-down');
    },
    remove() {
      this.$emit('remove');
    },
    confirmRemove() {
      if (this.field.confirmRemove) {
        this.removeMessage = true;
      } else {
        this.remove()
      }
    },
    changeVisibility() {
      this.$emit('change-visibility');
    },
    toggleAddBlockPopup() {
      Nova.$emit('nova-flexible-content.add-block-clicked');

      this.addBlockPopupVisible = !this.addBlockPopupVisible;

      this.$nextTick(() => {
        this.addBlockPopper?.update()
      })
    },
    duplicateBlock() {
      this.$emit('duplicate')
    }
  },
}
</script>

<style>
.group-control:focus {
  outline: none;
}

.group-control:hover {
  color: rgb(var(--colors-primary-400));
}

.confirm-message {
  position: absolute;
  overflow: visible;
  right: 38px;
  bottom: 0;
  width: auto;
  border-radius: 4px;
  padding: 6px 7px;
  border: 1px solid #B7CAD6;
  background-color: var(--20);
  white-space: nowrap;
}

[dir=rtl] .confirm-message {
  right: auto;
  left: 35px;
}

.confirm-message .text-danger {
  color: #ee3f22;
}

.rounded-l {
  border-top-left-radius: 0.25rem; /* 4px */
  border-bottom-left-radius: 0.25rem; /* 4px */
}

.rounded-t-lg {
  border-top-right-radius: 0.5rem; /* 8px */
  border-top-left-radius: 0.5rem; /* 8px */
}

.rounded-b-lg {
  border-bottom-left-radius: 0.5rem; /* 8px */
  border-bottom-right-radius: 0.5rem; /* 8px */
}

.box-content {
  box-sizing: content-box;
}

.grow {
  flex-grow: 1;
}

.grow-0 {
  flex-grow: 0;
}

.shrink {
  flex-shrink: 1;
}

.shrink-0 {
  flex-shrink: 0;
}
</style>
