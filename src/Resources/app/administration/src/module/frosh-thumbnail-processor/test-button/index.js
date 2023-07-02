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
            let configData = this.$parent;
            for (let i = 0; i < 20; i++) {
                if (typeof configData.currentSalesChannelId != "undefined") {
                    return configData.currentSalesChannelId;
                }

                configData = configData.$parent;
            }

            throw "Can not get pluginConfigData";
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

        saveAndCheck() {
            this.isLoading = true;
            this.systemConfigSaveAll();
        },

        check() {
            const me = this;

            me.thumbnailProcessorTest.getUrl(this.pluginSalesChannelId).then((res) => {
                if (res.url) {
                    me.isSuccessful = true;

                    const img = document.createElement('img');
                    img.width = 200;
                    img.height = 200;

                    img.onload = function() {
                        if (img.naturalWidth !== 200) {
                            me.showError(me.$tc('thumbnail-processor.test.error.noResize'), res.url);
                        }
                    };

                    img.onerror = function() {
                        me.showError(me.$tc('thumbnail-processor.test.error.general'), res.url);
                    };

                    img.src = res.url;

                    const testElement = document.querySelector('[name="FroshPlatformThumbnailProcessor.config.test"]');
                    const testImage = testElement.querySelector('.frosh-thumbnail-processor-testimage img');

                    if (testImage) {
                        testImage.replaceWith(img);
                    } else {
                        const testImageContainer = document.createElement('p');
                        testImageContainer.classList.add('frosh-thumbnail-processor-testimage');
                        testImageContainer.appendChild(img);
                        testElement.appendChild(testImageContainer);
                    }
                } else {
                    me.showError(me.$tc('thumbnail-processor.test.error.general'));
                }

                setTimeout(() => {
                    this.isLoading = false;
                }, 2500);
            });
        },

        systemConfigSaveAll() {
            const me = this;
            let el = this.$parent;

            for (let i = 0; i < 30; i++) {
                if (typeof el.$refs.systemConfig != "undefined") {
                    return el.$refs.systemConfig.saveAll()
                        .then(() => {
                            me.check();
                        })
                }

                el = el.$parent;
            }

            throw "Can not get systemConfig";
        }
    }
})
