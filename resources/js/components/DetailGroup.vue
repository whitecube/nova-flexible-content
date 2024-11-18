<template>
    <div v-if="!previewStylesheet" :class="componentStyle" :dusk="'detail-'+attribute+'-'+index">
        <div :class="titleStyle" v-if="group.title">
            <span class="block float-left border-r border-gray-100 dark:border-gray-700  pr-4 mr-4"><!--
             --><span class="text-60 text-xs">#</span><!--
             --><span class="text-80">{{index+1}}</span>
            </span>
            <span class="font-bold">{{group.title}}</span>
        </div>
        <component
            v-for="(item, index) in group.fields"
            :key="index"
            :is="'detail-' + item.component"
            :resource-name="resourceName"
            :resource-id="resourceId"
            :field="item"
            :validation-errors="null"
            :class="{ 'remove-bottom-border': index == group.fields.length - 1 }"
        />
    </div>    
    <preview-iframe v-else-if="initialPreviewHtml" :stylesheet="previewStylesheet" :flexible_key="group.key" :initialPreviewHtml="initialPreviewHtml" :updatedPreviewHtml="updatedPreviewHtml" />
</template>

<script>
import PreviewIframe from './PreviewIframe';

export default {
    components: {PreviewIframe},

    props: ['attribute', 'group', 'index', 'last', 'resource', 'resourceName', 'resourceId', 'previewStylesheet'],

    computed: {
        componentStyle() {
            return this.last ? [] : ['border-b border-50 pb-4 mb-4'];
        },
        titleStyle() {
            return ['pb-4', 'border-b', 'border-gray-100', 'dark:border-gray-700'];
        }
    },

    data() {
        return {
            form: new FormData(),
            initialPreviewHtml: null,
            updatedPreviewHtml: null,
        }
    },

    mounted() {
        this.$nextTick(() => {
            this.group.fields.forEach((field) => {
                this.form.set("__key", this.group.key);
                this.form.set(field.attribute, ((typeof field.value === 'object' && field.value !== null) ?
                    JSON.stringify(field.value) : (field.value ?? '')));
                });
            this.getPreview();
        });

    },

    methods: {
         getPreview() {
            fetch(
                `/nova-vendor/flexible/view/${this.resourceName}/${this.resourceId}/${this.attribute}/${this.group.name}`,
                {
                    method: "post",
                    body: this.form,
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]'
                        ).content,
                    },
                }
            )
                .then((response) => response.json())
                .then((json) => {

                    if(!this.initialPreviewHtml || json.has_uploads) {
                        this.initialPreviewHtml = json.view;
                    }
                    else {
                        this.updatedPreviewHtml = json.view;
                    }
                });
        },
    }
}
</script>
