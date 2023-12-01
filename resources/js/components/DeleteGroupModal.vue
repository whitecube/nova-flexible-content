<template>
    <Modal :show="true">
    <form
      @submit.prevent="$emit('confirm')"
      class="mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden"
    >
      <slot>
        <ModalHeader v-text="__('Delete Group')" />
        <ModalContent>
            <p class="leading-normal" v-if="message">
                {{ message }}
            </p>
            <p class="leading-normal" v-else>
                {{ __('Are you sure you want to delete this group?') }}
            </p>
        </ModalContent>
      </slot>

      <ModalFooter>
        <div class="ml-auto">
          <link-button
            type="button"
            data-testid="cancel-button"
            dusk="cancel-delete-button"
            @click.prevent="this.$emit('close')"
            class="mr-3"
          >
            {{ no }}
          </link-button>

          <Button
              type="submit"
              ref="confirmButton"
              dusk="confirm-delete-button"
              :loading="working"
              :disabled="working"
              state="danger"
              :label="yes"
          />
        </div>
      </ModalFooter>
    </form>
    </Modal>
</template>

<script>
import { Button } from 'laravel-nova-ui'

export default {
    components: {
        Button,
    },

    props: ['message', 'yes', 'no'],

    emits: ['close', 'confirm'],

    /**
     * Mount the component.
     */
    mounted() {
        this.$nextTick(() => {
            // this.$refs.confirmButton.button.focus()
        })
    },
}
</script>
