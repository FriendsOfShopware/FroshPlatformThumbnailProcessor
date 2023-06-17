(()=>{var t=`{% block frosh_thumbnail_processor_info_texts %}
    <div class="frosh-thumbnail-processor-info-texts">
        <p>
            Hint: You can set the Configs per SalesChannel and you must not use all variables.
        </p>

        <p>
            Available variables for the ThumbnailPattern:<br>
            <b>{mediaUrl}</b>: f.e. https://cdn.test.de/<br>
            <b>{mediaPath}</b>: f.e. media/image/5b/6d/16/tea.png<br>
            <b>{width}</b>: f.e. 800
        </p>

        <p>
            Find pattern in the discussions category at GitHub:<br>
            <a href="https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/discussions/categories/patterns" target="_blank">
                Github Category Patterns
            </a>
        </p>
    </div>
{% endblock %}
`;var{Component:s}=Shopware;s.register("frosh-thumbnail-processor-info-texts",{template:t});})();
