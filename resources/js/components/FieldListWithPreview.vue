<template>
    <div>
        <div @click="$emit('group-selected')"
            class="relative" style="height: 960px;" v-if="initialPreviewHtml">
            <div class="absolute z-10 inset-0 ring-inset hover:ring" :class="{'pointer-events-none ring ring-primary-200' : selectedGroup, 'ring-primary-100' : !selectedGroup}"></div>
            <iframe
                :style="{
                    transform: `translate(-50%,-50%) scale(${scale})`,
                }"
                class=" absolute block left-1/2 top-1/2 h-full transform w-screen"
                ref="iframe"
                :srcdoc="`
                <html>
                <head>
                    <base target='_blank' />
                    <link rel='stylesheet' href='${stylesheet}' />
                    <script type='module'>
                    window.addEventListener('message', (event) => {
                        document.body.innerHTML = event.data;
                        window.parent.postMessage('${flexible_key}', '*');
                    });
                    window.addEventListener('load', (event)=> {
                        window.parent.postMessage('${flexible_key}', '*');
                        
                    });
                    window.addEventListener('resize', (event)=> {
                        window.parent.postMessage('${flexible_key}', '*');
                    });
                    </script>
                    <script src='//unpkg.com/alpinejs' defer></script>
                </head>
                <body>
                    ${ initialPreviewHtml }
                </body>
                </html>`"
            >
            </iframe>
        </div>
         <div v-show="selectedGroup"
            class="absolute top-0 left-0 md:w-1/5 bottom-0 h-full  bg-gray-50 overflow-y-scroll self-stretch">
           
            <div class="w-full py-4 overflow-hidden">
                <div class="px-6 py-6  flex flex-row items-center justify-between">
                    <h3 class="text-lg font-semibold">{{  title }}</h3>
                    <button :aria-label="`Close ${ title }`" @click.prevent="$emit('group-selected')">  <icon type="x-circle" class="align-top" width="36" height="36" /></button>
                </div>
                <div
                    class="fields relative divide-y divide-gray-100 dark:divide-gray-700"
                    style="width: 125%; margin-left: -0.25rem; margin-right: -0.25rem;">
                    <component
                        v-for="(item, index) in stackedFields"
                        :key="index"
                        :is="'form-' + item.component"
                        :resource-name="resourceName"
                        :resource-id="resourceId"
                        fullWidthContent="true"
                        @input="onInput($event, item)"
                        :field="item"
                        :errors="errors"
                        :mode="mode"
                        :show-help-text="item.helpText != null"
                        :class="{
                            'remove-bottom-border': index == fields.length - 1,
                        }"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { tsImportEqualsDeclaration } from '@babel/types';
import _, { map } from 'underscore';

export default {
    props: {
        layoutName: null,
        resourceName: null,
        resourceId: null,
        fieldName: null,
        containerStyle: [],
        fields: {},
        errors: {},
        stylesheet: null,
        flexible_key: null,
        fullScreen: false,
        selectedGroup: false,
        title: null
    },

    data() {
        return {
            form: new FormData(),
            scale: 1,
            initialPreviewHtml: null

        };
    },

    computed: {
        stackedFields() {
            return this.fields.map((field) => {
                field.stacked = true;
                return field;
            });
        },
    },

    mounted() {
        this.form.append("__key", this.flexible_key);

        this.$nextTick(() => {
            this.fields.forEach((field) => {
                this.form.set(field.attribute, field.value ?? '');
                    field.fill(this.form);                
            });
            this.getPreview();
        });

        window.addEventListener(
            "message",
            (event) => {
                if(event.data == this.flexible_key) {
                    this.setIframeHeight(this.$refs.iframe);
                }
            },
            false
        );

        window.addEventListener(
            "resize",
            () => {
                this.setIframeScale(this.$refs.iframe);
                this.$nextTick(() => {
                    this.setIframeHeight(this.$refs.iframe);
                });
            },
            false
        );    
    },

    watch: {
        fullScreen: function() {
            this.$nextTick(() => {
                this.setIframeScale(this.$refs.iframe);
                this.$nextTick(() => {
                    this.setIframeHeight(this.$refs.iframe);
                });
            });
        },
    },

    methods: {
        setIframeScale(iframe) {
            if(iframe) {
                let width = Math.min(window.innerWidth, 1800);
                this.scale = iframe.parentNode.clientWidth / width;
                this.$refs.iframe.style.width = width + "px";

            }
        },
        
        setIframeHeight(iframe) {
            if(iframe) {

                this.$refs.iframe.style.height =
                    iframe.contentWindow.document.body.scrollHeight + "px";
                this.$refs.iframe.parentNode.style.height =
                    iframe.contentWindow.document.body.scrollHeight * this.scale +
                    "px";
            }
        },

        onInput: _.debounce(function debounceRead(evt, updatedField) {
            this.update(updatedField);
        }, 150),

        update(updatedField) {
            let field = this.fields.find(
                (field) => field.uniqueKey == updatedField.uniqueKey
            );
            this.form.delete(field.attribute);
            field.fill(this.form);
            this.getPreview();
        },

        getPreview() {
            fetch(
                `/nova-vendor/flexible/view/${this.resourceName}/${this.resourceId}/${this.fieldName}/${this.layoutName}`,
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

                    // Reset the form so we don't keep uploading files on every refresh
                    this.form = new FormData();
                    this.form.append("__key", this.flexible_key);
                    this.fields.forEach((field) => {
                        this.form.set(field.attribute, json.data[field.attribute] ?? '');
                    });

                    if(!this.initialPreviewHtml || json.has_uploads) {
                        this.initialPreviewHtml = json.view;
                        this.$nextTick(() => {
                            this.setIframeScale(this.$refs.iframe);
                        });
                    }
                    else {
                        this.$refs.iframe.contentWindow.postMessage(json.view, "*");
                    }
                });
        },
    },
};
</script>

<style>
.w-screen {
    width: 100vw;
}

.left-1\/2 {
    left: 50%;
}

.top-1\/2 {
    top: 50%;
}

.self-stretch {
    align-self: stretch;
}

.fields .md\:px-8,
.fields .px-8 {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
}

.fields label {
    /* font-weight: bold; */
}

.fields label .flex {
    flex-direction: column;
    gap: 0.5rem;
}

.fields .form-file {
    width: 100%;
}

.fields .md\:w-1\/4,
.fields .w-1\/2 {
    width: 100%
}

.nsr-w-full.nsr-flex.nsr-border-b.nsr-py-2 {
    display: none;
}

.fields .simple-repeatable-fields-wrapper {
    flex-direction: column;
    gap: 0.5em;
}

.fields .simple-repeatable.form-field .simple-repeatable-row {
    margin-left: 0;
    padding-left: 0;
    width: 100%;
}

.fields .simple-repeatable.form-field>:nth-child(2) {
    margin-right: 0;
    width: 80% !important;
}

.fields .simple-repeatable.form-field .simple-repeatable-row .delete-icon, .simple-repeatable.form-field .simple-repeatable-row .vue-draggable-handle {
    margin-right: 0;
}

.simple-repeatable.form-field .simple-repeatable-row>.simple-repeatable-fields-wrapper .translatable-field>div:not(:first-child)>div, .simple-repeatable.form-field .simple-repeatable-row>.simple-repeatable-fields-wrapper>* {
    margin-right: 0;
}

.-top-6 {
    top: -1.5rem;
}

.-right-6 {
    right: -1.5rem;
}

.-mx-8 {
    margin-left: -2rem;
    margin-right: -2rem;
}

.-mt-6 {
    margin-top: -1.5rem;
}

.hover\:ring:hover {
    --tw-ring-offset-shadow: var(--tw-ring-inset) 0 0 0 var(--tw-ring-offset-width) var(--tw-ring-offset-color);
    --tw-ring-shadow: var(--tw-ring-inset) 0 0 0 calc(3px + var(--tw-ring-offset-width)) var(--tw-ring-color);
    box-shadow: var(--tw-ring-offset-shadow), var(--tw-ring-shadow), var(--tw-shadow, 0 0 #0000);
}
</style>

