<template>
    <div class="flex overflow-hidden flex-row bg-gray-50 dark:bg-gray-900 items-center">
        <div class="flex-shrink w-full">
            <div class="relative" style="height: 480px;" v-if="initialPreviewHtml">
                <iframe
                    :style="{
                        transform: `translate(-50%,-50%) scale(${scale})`,
                    }"
                    class="bg-white absolute block left-1/2 top-1/2 h-full transform w-screen"
                    ref="iframe"
                    :srcdoc="`
                  <html>
                    <head>
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
                    </head>
                    <body>
                        ${ initialPreviewHtml }
                    </body>
                  </html>`"
                >
                </iframe>
            </div>
        </div>
        <div
            class="bg-white dark:bg-gray-800  shadow-lg relative border-l border-gray-200 dark:border-gray-700 overflow-y-scroll self-stretch"
            :style="{
                height: fullScreen ? 'calc(100vh - 2.3rem)' : '',
                width: '24rem',
            }"
        >
            <div
                class="py-4 fields relative divide-y divide-gray-100 dark:divide-gray-700"
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

        this.fields.forEach((field) => {
            // field.fill(this.form);
            this.form.set(field.attribute, field.value ?? '');
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

        this.getPreview();
    },

    watch: {
        fullScreen: function() {
            this.$nextTick(() => {
                this.setIframeScale(this.$refs.iframe);
            });
        },
    },

    methods: {
        setIframeScale(iframe) {
            if(iframe) {
                this.scale = iframe.parentNode.clientWidth / window.innerWidth;
                this.$refs.iframe.style.width = window.innerWidth + "px";
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
                    this.form = new FormData();
                    this.form.append("__key", this.flexible_key);
             
                    this.fields.forEach((field) => {
                        console.log(field.attribute, json.data, json.data[field.attribute]);
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

.fields .flex {
    flex-direction: column;
    gap: 0.5rem;
}
</style>
