(()=>{var a=`{% block frosh_thumbnail_processor_info_texts %}
    <div class="frosh-thumbnail-processor-info-texts">
        <p>
            Hint: You can set the configuration per SalesChannel and you don't need to use all variables.
        </p>

        <p>
            Available variables for the ThumbnailPattern:<br>
            <b>{mediaUrl}</b>: e.g. https://cdn.test.de/<br>
            <b>{mediaPath}</b>: e.g. media/image/5b/6d/16/tea.png<br>
            <b>{width}</b>: e.g. 800
        </p>

        <p>
            Find patterns in the discussion category 'Patterns' of the GitHub repository:<br>
            <a href="https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/discussions/categories/patterns" target="_blank">
                GitHub Category 'Patterns'
            </a>
        </p>
    </div>
{% endblock %}
`;var{Component:m}=Shopware;m.register("frosh-thumbnail-processor-info-texts",{template:a});var c=`<div>
    <sw-button-process
        :isLoading="isLoading"
        :processSuccess="isSuccessful"
        @process-finish="finish"
        @click="saveAndCheck"
    >{{ btnLabel }}</sw-button-process>
</div>
`;var{Component:f,Mixin:d}=Shopware;f.register("thumbnailprocessor-test",{template:c,props:["btnLabel"],inject:["thumbnailProcessorTest"],mixins:[d.getByName("notification")],data(){return{isLoading:!1,isSuccessful:!1}},computed:{pluginSalesChannelId(){let e=this.$parent;for(let t=0;t<20;t++){if(typeof e.currentSalesChannelId<"u")return e.currentSalesChannelId;e=e.$parent}throw"Can not get pluginConfigData"}},methods:{finish(){this.isSuccessful=!1},showError(e,t){this.isSuccessful=!1,t&&(e+=" sample url: "+t),this.createNotificationError({title:this.$tc("thumbnail-processor.test.title"),message:e})},saveAndCheck(){this.isLoading=!0,this.systemConfigSaveAll()},check(){let e=this;e.thumbnailProcessorTest.getUrl(this.pluginSalesChannelId).then(t=>{if(t.url){e.isSuccessful=!0;let s=document.createElement("img");s.width=200,s.height=200,s.onload=function(){s.naturalWidth!==200&&e.showError(e.$tc("thumbnail-processor.test.error.noResize"),t.url)},s.onerror=function(){e.showError(e.$tc("thumbnail-processor.test.error.general"),t.url)},s.src=t.url;let r=document.querySelector('[name="FroshPlatformThumbnailProcessor.config.test"]'),n=r.querySelector(".frosh-thumbnail-processor-testimage img");if(n)n.replaceWith(s);else{let o=document.createElement("p");o.classList.add("frosh-thumbnail-processor-testimage"),o.appendChild(s),r.appendChild(o)}}else e.showError(e.$tc("thumbnail-processor.test.error.general"));setTimeout(()=>{this.isLoading=!1},2500)})},systemConfigSaveAll(){let e=this,t=this.$parent;for(let s=0;s<30;s++){if(typeof t.$refs.systemConfig<"u")return t.$refs.systemConfig.saveAll().then(()=>{e.check()});t=t.$parent}throw"Can not get systemConfig"}}});var l=Shopware.Classes.ApiService,{Application:h}=Shopware,i=class extends l{constructor(t,s,r="thumbnail-processor-test"){super(t,s,r)}getUrl(t){let s=this.getBasicHeaders({});return this.httpClient.post(`_action/${this.getApiBasePath()}/get-sample-image`,{salesChannelId:t},{headers:s}).then(r=>l.handleResponse(r))}};h.addServiceProvider("thumbnailProcessorTest",e=>{let t=h.getContainer("init");return new i(t.httpClient,e.loginService)});})();
