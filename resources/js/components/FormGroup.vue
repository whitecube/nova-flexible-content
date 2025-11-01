<template>
  <div class="relative mb-4 pb-1" :id="group.key">
    <div class="w-full shrink">
      <div :class="titleStyle" v-if="group.title">
        <div
          class="h-8 leading-normal h-full flex items-center box-content"
          :class="{
            'border-b border-gray-200 dark:border-gray-700 ': !collapsed,
          }"
        >
          <button
            dusk="expand-group"
            type="button"
            class="shrink-0 group-control btn border-r border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
            :title="__('Expand')"
            @click.prevent="expand"
            v-if="collapsed"
          >
            <Icon 
              name="plus"
              type="micro" 
              class="align-top" />
          </button>
          <button
            dusk="collapse-group"
            type="button"
            class="group-control btn border-r border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
            :title="__('Collapse')"
            @click.prevent="collapse"
            v-else
          >
            <Icon 
              name="minus"
              type="micro" 
              class="align-top" />
          </button>

          <p class="text-80 grow px-4">
            <span class="mr-3 font-semibold">#{{ index + 1 }}</span>
            <span v-if="group.popover">
              <button type="button"
                      tabindex="0"
                      :popovertarget="group.name + '-popover-' + group.key"
                      popovertargetaction="toggle"
                      style="cursor: pointer;"
                      data-popover-toggle
                      :data-image-url="field.popover"
                      :data-slug="group.name"
                      data-image-check="pending">
                <span v-text="title"></span>
              </button>

              <div popover :id="group.name + '-popover-' + group.key" class="bg-white p-1 rounded-lg shadow w-24 ring ring-inset ring-black/10">
                <img :src="group.popover" class="w-full" :alt="title">
              </div>
            </span>
            <span v-else>{{ title }}</span>
          </p>

          <div class="flex" v-if="!readonly">
            <button
              dusk="drag-group"
              type="button"
              class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center nova-flexible-content-drag-button"
              :title="__('Drag')"
            >
              <Icon 
                name="selector" 
                type="micro"
                class="align-top" />
            </button>
            <button
              dusk="move-up-group"
              type="button"
              class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
              :title="__('Move up')"
              @click.prevent="moveUp"
            >
              <Icon 
                name="arrow-up" 
                type="micro"
                class="align-top" />
            </button>
            <button
              dusk="move-down-group"
              type="button"
              class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
              :title="__('Move down')"
              @click.prevent="moveDown"
            >
              <Icon
                name="arrow-down"
                class="align-top"
                type="micro" />
            </button>
            <button
              dusk="title-group"
              type="button"
              class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
              :title="__('Title')"
              @click.prevent="setInternalTitle"
            >
              <Icon
                name="document-text"
                class="align-top"
                type="mini" />
            </button>
            <button
              dusk="delete-group"
              type="button"
              class="group-control btn border-l border-gray-200 dark:border-gray-700 w-8 h-8 flex justify-center items-center"
              :title="__('Delete')"
              @click.prevent="confirmRemove"
            >
              <Icon 
                name="trash" 
                class="align-top" 
                type="micro" />
            </button>
            <delete-flexible-content-group-modal
              v-if="removeMessage"
              @confirm="remove"
              @close="removeMessage = false"
              :message="field.confirmRemoveMessage"
              :yes="field.confirmRemoveYes"
              :no="field.confirmRemoveNo"
            />
            
            <!-- Internal Title Modal -->
            <Modal
              :show="showTitleModal"
              @close-via-escape="cancelInternalTitle"
              role="dialog"
              size="md"
            >
              <form @submit.prevent="saveInternalTitle" class="bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
                <ModalHeader class="flex items-center">
                  <h2 class="text-xl text-gray-800 dark:text-gray-200">
                    {{ __('Set Internal Title') }}
                  </h2>
                </ModalHeader>
                
                <ModalContent class="px-8 py-6">
                  <div class="mb-6">
                    <label class="inline-block text-gray-800 dark:text-gray-200 mb-2">
                      {{ __('Internal Title') }}
                    </label>
                    <input
                      ref="titleInput"
                      v-model="tempInternalTitle"
                      type="text"
                      class="w-full form-control form-input form-control-bordered"
                      :placeholder="__('Enter internal title...')"
                      @keyup.enter="saveInternalTitle"
                      @keyup.escape="cancelInternalTitle"
                    />
                  </div>
                </ModalContent>
                
                <ModalFooter>
                  <div class="ml-auto">
                    <Button
                      type="button"
                      @click="cancelInternalTitle"
                      class="mr-3"
                    >
                      {{ __('Cancel') }}
                    </Button>
                    
                    <Button
                      type="submit"
                      :loading="false"
                    >
                      {{ __('Save') }}
                    </Button>
                  </div>
                </ModalFooter>
              </form>
            </Modal>
          </div>
        </div>
      </div>
      <div :class="containerStyle">
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
          :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { mapProps } from "laravel-nova";
import { Icon, Button } from "laravel-nova-ui";

export default {
  components: { Icon, Button },
  props: {
    errors: {},
    group: {},
    index: {},
    field: {},
    ...mapProps(["resourceName", "resourceId", "mode"]),
  },

  emits: ["move-up", "move-down", "remove", "set-internal-title"],

  data() {
    return {
      removeMessage: false,
      collapsed: this.group.collapsed,
      readonly: this.group.readonly,
      showTitleModal: false,
      tempInternalTitle: '',
      internalTitle: this.group.internalTitle || '',
    };
  },

  computed: {
    titleStyle() {
      let classes = [
        "border-t",
        "border-r",
        "border-l",
        "border-gray-200",
        "dark:border-gray-700",
        "rounded-t-lg",
      ];

      if (this.collapsed) {
        classes.push("border-b rounded-b-lg");
      }

      return classes;
    },
    containerStyle() {
      let classes = [
        "grow",
        "border-b",
        "border-r",
        "border-l",
        "border-gray-200",
        "dark:border-gray-700",
        "rounded-b-lg",
      ];

      if (!this.group.title) {
        classes.push("border-t");
        classes.push("rounded-tr-lg");
      }

      if (this.collapsed) {
        classes.push("hidden");
      }

      return classes;
    },

    title() {
      if (this.internalTitle) {
        return `${this.internalTitle} [${this.group.title}]`;
      } else {
        return this.group.title;
      }
    }
  },

  methods: {
    /**
     * Move this group up
     */
    moveUp() {
      this.$emit("move-up");
    },

    /**
     * Move this group down
     */
    moveDown() {
      this.$emit("move-down");
    },

    /**
     * Remove this group
     */
    remove() {
      this.$emit("remove");
    },

    /**
     * Confirm remove message
     */
    confirmRemove() {
      if (this.field.confirmRemove) {
        this.removeMessage = true;
      } else {
        this.remove();
      }
    },

    /**
     * Expand fields
     */
    expand() {
      this.collapsed = false;
    },

    /**
     * Collapse fields
     */
    collapse() {
      this.collapsed = true;
    },

    /**
     * Set internal title for this group
     */
    setInternalTitle() {
      this.tempInternalTitle = this.internalTitle;
      this.showTitleModal = true;
      
      // Focus the input field after the modal is shown
      this.$nextTick(() => {
        if (this.$refs.titleInput) {
          this.$refs.titleInput.focus();
        }
      });
    },

    /**
     * Save the internal title
     */
    saveInternalTitle() {
      this.internalTitle = this.tempInternalTitle;
      this.showTitleModal = false;
      
      // Update the group object to persist the internal title (Vue 3 compatible)
      this.group.internalTitle = this.internalTitle;
      this.$emit("set-internal-title", this.internalTitle);
    },

    /**
     * Cancel internal title editing
     */
    cancelInternalTitle() {
      this.tempInternalTitle = '';
      this.showTitleModal = false;
    },
  },
};
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
  border: 1px solid #b7cad6;
  background-color: var(--20);
  white-space: nowrap;
}
[dir="rtl"] .confirm-message {
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
