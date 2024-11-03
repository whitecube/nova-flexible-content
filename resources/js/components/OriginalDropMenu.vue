<template>
  <div class="relative" v-if="layouts" ref="dropdownContainer">
    <DefaultButton
        dusk="toggle-layouts-dropdown-or-add-default"
        type="button"
        tabindex="0"
        ref="dropdownButton"
        @click="toggleLayoutsDropdownOrAddDefault"
        v-if="isBelowLayoutLimits"
    >
      <span>{{ field.button }}</span>
    </DefaultButton>

    <teleport to="body">
      <div v-show="isLayoutsDropdownOpen && layouts.length > 1"
           ref="dropdown"
           class="rounded-lg shadow-lg max-w-xs max-h-search overflow-y-auto border border-40"
      >
        <ul class="list-reset">
          <li v-for="layout in filteredLayouts" class="border-b border-gray-100 dark:border-gray-700"
              :key="'add-'+layout.name">
            <a
                :dusk="'add-' + layout.name"
                @click="addGroup(layout)"
                class="cursor-pointer flex items-center hover:bg-gray-50 dark:hover:bg-gray-900 py-2 px-3 no-underline font-normal bg-white dark:bg-gray-800"
            >
              <span class="text-90">{{ layout.title }}</span>
            </a>
          </li>
        </ul>
      </div>
    </teleport>
  </div>
</template>

<script>

import {createPopper} from "@popperjs/core";

export default {
  props: ['layouts', 'field', 'resourceName', 'resourceId', 'resource', 'errors', 'limitCounter', 'limitPerLayoutCounter'],

  emits: ['addGroup'],

  data() {
    return {
      isLayoutsDropdownOpen: false,
      popper: null,
    };
  },

  computed: {
    filteredLayouts() {
      return this.layouts.filter(layout => {
        const count = this.limitPerLayoutCounter[layout.name];

        return count === null || count > 0 || typeof count === 'undefined';
      });
    },

    isBelowLayoutLimits() {
      return (this.limitCounter > 0 || this.limitCounter === null) && this.filteredLayouts.length > 0;
    },
  },

  watch: {
    isLayoutsDropdownOpen: function (value) {
      if (!value) {
        return;
      }
      const {bottom: dropdownBottom} = this.$refs.dropdown.getBoundingClientRect();

      if (dropdownBottom > window.innerHeight) {
        this.updateDropdownPosition('top-start')
      } else {
        this.updateDropdownPosition('bottom-start')
      }
    }
  },

  methods: {
    toggleLayoutsDropdownOrAddDefault() {
      if (this.layouts.length === 1) {
        return this.addGroup(this.layouts[0]);
      }

      this.isLayoutsDropdownOpen = !this.isLayoutsDropdownOpen;
    },

    addGroup(layout) {
      if (!layout) return;

      this.$emit('addGroup', layout);
      Nova.$emit('nova-flexible-content-add-group', layout);
      this.isLayoutsDropdownOpen = false;
    },

    updateDropdownPosition(placement) {
      if (this.popper) {
        this.popper.update({placement})

        return;
      }

      this.popper = createPopper(this.$refs.dropdownContainer, this.$refs.dropdown, {
        placement,
        modifiers: [{name: 'offset', options: {offset: [0, 8]}}],
      })
    }
  }
}
</script>


<style>
.top-full {
  top: 100%
}

.pin-b {
  top: 100%;
  bottom: auto;
}

.pin-t {
  top: auto;
  bottom: 100%;
}
</style>
