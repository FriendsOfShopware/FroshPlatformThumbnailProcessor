const {Component, Mixin} = Shopware;

Component.register('frosh-thumbnail-processor-config-restriction', {
    template: ' ', // we need content to be created

    created() {
        this.checkAndHideSetting();
    },

    updated() {
        this.checkAndHideSetting();
    },

    methods: {
        checkAndHideSetting() {
            const fields = document.querySelectorAll('input[name^="FroshPlatformThumbnailProcessor.config"],.sw-plugin-config__save-action');

            if (this.pluginConfigData().currentSalesChannelId) {
                fields.forEach(el => {
                    el.setAttribute('disabled', 'disabled');
                });
            } else {
                fields.forEach(el => {
                    el.removeAttribute('disabled');
                });
            }
        },

        pluginConfigData() {
            let config = this.$parent.$parent.$parent.actualConfigData;
            if (config) {
                return this.$parent.$parent.$parent;
            }

            config = this.$parent.$parent.$parent.$parent.actualConfigData;
            if (config) {
                return this.$parent.$parent.$parent.$parent;
            }

            return this.$parent.$parent.$parent.$parent.$parent;
        }
    },

})
