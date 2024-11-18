<template>
    <div class="relative" :style="{
                height: `${iframeParentHeight}px`,
            }">
        <iframe
            :style="{
                transform: `translate(-50%,-50%) scale(${scale})`,
                height: `${iframeHeight}px`,
                width: `${iframeWidth}px`,
            }"
            class=" absolute block left-1/2 top-1/2 h-full transform w-screen"
            ref="iframe"
            :srcdoc="`
            <html>
            <head>
                <base target='_blank' />
                <link rel='stylesheet' href='${stylesheet}' />
                <script type='module'>

                function findVhElements() {
                    const stylesheets = Array.from(document.styleSheets);
                    const hasVh = str => str && str.includes('vh');
                    return stylesheets.reduce((acc, sheet) => {
                    const targetRules = Array.from(sheet.cssRules).filter(({ style }) =>
                        style && ((hasVh(style.minHeight) && parseInt(style.minHeight) > 100) || (hasVh(style.height) && parseInt(style.height) > 100))
                    );
                    if (!targetRules.length) return acc;
                    return acc.concat(
                        targetRules.map(({ selectorText, style }) => {
                            
                                return { selector: selectorText, height: style.height, minHeight: style.minHeight };
                        
                        }).filter(el => el));
                    }, []);
                }

                function resizeVhElements() {
                    findVhElements().forEach((item) => {
                        console.log('resizing all: ' + item.selector);
                        document.querySelectorAll(item.selector).forEach(
                            (elem) =>  elem.style.height = parseInt(item.height) / 100  *  document.body.clientHeight + 'px'
                        );
                    });
                }
                
                window.addEventListener('message', (event) => {
                    document.body.innerHTML = event.data;
                    window.parent.postMessage('${flexible_key}', '*');
                    resizeVhElements();
                });
                window.addEventListener('load', (event)=> {
                    window.parent.postMessage('${flexible_key}', '*');
                    resizeVhElements();
                });
                window.addEventListener('resize', (event)=> {
                    window.parent.postMessage('${flexible_key}', '*');
                });
                </script>
                <script src='//unpkg.com/alpinejs' defer></script> 
            </head>
            <body class='w-screen overflow-hidden'>
                ${ initialPreviewHtml }
            </body>
            </html>`"
        >
        </iframe>
    </div>
</template>

<script>

export default {
    props: {
        stylesheet: null,
        flexible_key: null,
        fullScreen: false,
        initialPreviewHtml: null,
        updatedPreviewHtml: null,
    },

    data() {
        return {
            scale: 1,
            iframeHeight: 960,
            iframeWidth: 0.8 * window.innerWidth, // Based on the preview being ~80% of screen width. Effectively this scales the preview down to the available space.
            iframeParentHeight: 1200
        };
    },

    mounted() {

        window.addEventListener(
            "message",
            (event) => {
                if(event.data == this.flexible_key) {
                    this.setIframeScaleThenHeight();
                }
            },
            false
        );

        window.addEventListener(
            "resize",
            () => {
                this.setIframeScaleThenHeight();
            },
            false
        );    
    },

    watch: {
        fullScreen: function() {
            this.$nextTick(() => {
                this.setIframeScaleThenHeight();
            });
        },

        updatedPreviewHtml: function () {
            this.$refs.iframe.contentWindow.postMessage(this.updatedPreviewHtml, "*");
        }
    },

    methods: {
        setIframeScaleThenHeight() {
            if(!this.$refs.iframe) return false;

            this.setIframeScale();
            this.$nextTick(() => {
                this.setIframeHeight();
            });
        },
    
        setIframeScale() {
                let width = Math.min(window.innerWidth, 1800);
                let unroundedScale = this.$refs.iframe.parentNode.clientWidth / width;
                this.scale = Math.floor(unroundedScale * 1000)/1000;
                this.iframeWidth = width;
        },
        
        setIframeHeight() {
                this.iframeHeight =
                    this.$refs.iframe.contentWindow.document.body.clientHeight;                    
                this.iframeParentHeight =
                    this.iframeHeight * this.scale;
        },
    },
};
</script>