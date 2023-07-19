const {Component, Mixin} = Shopware;
import template from './test-button.html.twig';
import './style.css';

Component.register('thumbnailprocessor-test', {
    template,

    props: ['btnLabel'],
    inject: ['thumbnailProcessorTest'],

    mixins: [
        Mixin.getByName('notification')
    ],

    data() {
        return {
            isLoading: false,
            isSuccessful: false,
        };
    },

    computed: {
        pluginSalesChannelId() {
            const configComponent = this.getParentComponent();

            if (!configComponent) {
                throw "Can not get pluginConfigData";
            }

            return configComponent.currentSalesChannelId;
        }
    },

    methods: {
        finish() {
            this.isSuccessful = false;
        },

        showError(message, sampleUrl) {
            this.isSuccessful = false;

            if (sampleUrl) {
                message += ' sample url: ' + sampleUrl;
            }

            this.createNotificationError({
                title: this.$tc('thumbnail-processor.test.title'),
                message: message
            });
        },

        check() {
            this.isLoading = true;

            this.thumbnailProcessorTest.getUrl(this.pluginSalesChannelId).then((res) => {
                if (res.url) {
                    this.isSuccessful = true;
                    const me = this;
                    const img = document.createElement('img');
                    const testImageContainerElement = document.querySelector('#testimage-container');
                    const testImageElement = testImageContainerElement.querySelector('img');
                    const testResultElement = document.querySelector('#test-result');

                    img.src    = res.url;
                    img.width  = 200;
                    img.height = 200;
                    img.onload = function() {
                        if (img.naturalWidth !== 200) {
                            testResultElement.innerText = me.$tc('thumbnail-processor.test.error.noResize');
                            me.showError(me.$tc('thumbnail-processor.test.error.noResize'), res.url);
                        }
                    };
                    img.onerror = function() {
                        testImageElement.height = 0;
                        testImageElement.width = 0;
                        testResultElement.innerText = me.$tc('thumbnail-processor.test.error.general');
                        me.showError(me.$tc('thumbnail-processor.test.error.general'), res.url);
                    };

                    if (testImageElement) {
                        testImageElement.replaceWith(img);

                        return;
                    }

                    testImageContainerElement.appendChild(img);
                } else {
                    this.showError(this.$tc('thumbnail-processor.test.error.general'));
                }

                setTimeout(() => {
                    this.isLoading = false;
                }, 2500);
            });
        },

        systemConfigSaveAll() {
            this.isLoading = true;
            const configComponent = this.getParentComponent();

            if (!configComponent) {
                this.isLoading = false;

                throw "Can not get systemConfig";
            }

            configComponent.saveAll()
                .then(() => {
                    this.check();
                    this.isLoading = false;
                });
        },

        getParentComponent (component = this) {
            if (typeof component.actualConfigData !== 'undefined') {
                return component;
            }

            if (component.$parent) {
                return this.getParentComponent(component.$parent);
            }

            return null;
        },
    },
})
