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
`;var{Component:p}=Shopware;p.register("frosh-thumbnail-processor-info-texts",{template:a});var c=`<sw-card class="sub-card frosh-thumbnail-processor">
    <sw-button-process
            class="frosh-thumbnail-processor--test-button"
            :isLoading="isLoading"
            :processSuccess="isSuccessful"
            @process-finish="finish"
            @click="systemConfigSaveAll">
        {{ btnLabel }}
    </sw-button-process>

    <div class="testdata-container">
        <p id="test-result"></p>
        <div id="testimage-container"></div>
    </div>
</sw-card>
`;var{Component:f,Mixin:d}=Shopware;f.register("thumbnailprocessor-test",{template:c,props:["btnLabel"],inject:["thumbnailProcessorTest"],mixins:[d.getByName("notification")],data(){return{isLoading:!1,isSuccessful:!1}},computed:{pluginSalesChannelId(){let t=this.getParentComponent();if(!t)throw"Can not get pluginConfigData";return t.currentSalesChannelId}},methods:{finish(){this.isSuccessful=!1},showError(t,s,e){this.isSuccessful=!1,s&&(t+=" sample url: "+s),e.innerText=t,e.scrollIntoView(),this.createNotificationError({title:this.$tc("thumbnail-processor.test.title"),message:t})},check(){this.isLoading=!0,this.thumbnailProcessorTest.getUrl(this.pluginSalesChannelId).then(t=>{if(t.url){this.isSuccessful=!0;let s=this,e=document.createElement("img"),r=document.querySelector("#testimage-container"),i=r.querySelector("img"),n=document.querySelector("#test-result");if(e.src=t.url,e.width=200,e.height=200,e.onload=function(){e.naturalWidth!==200&&s.showError(s.$tc("thumbnail-processor.test.error.noResize"),t.url,n)},e.onerror=function(){i.height=0,i.width=0,s.showError(s.$tc("thumbnail-processor.test.error.general"),t.url,n)},i){i.replaceWith(e);return}r.appendChild(e)}else this.showError(this.$tc("thumbnail-processor.test.error.general"));setTimeout(()=>{this.isLoading=!1},2500)})},systemConfigSaveAll(){this.isLoading=!0;let t=this.getParentComponent();if(!t)throw this.isLoading=!1,"Can not get systemConfig";t.saveAll().then(()=>{this.check(),this.isLoading=!1})},getParentComponent(t=this){return typeof t.actualConfigData<"u"?t:t.$parent?this.getParentComponent(t.$parent):null}}});var l=Shopware.Classes.ApiService,{Application:h}=Shopware,o=class extends l{constructor(s,e,r="thumbnail-processor-test"){super(s,e,r)}getUrl(s){let e=this.getBasicHeaders({});return this.httpClient.post(`_action/${this.getApiBasePath()}/get-sample-image`,{salesChannelId:s},{headers:e}).then(r=>l.handleResponse(r))}};h.addServiceProvider("thumbnailProcessorTest",t=>{let s=h.getContainer("init");return new o(s.httpClient,t.loginService)});})();
