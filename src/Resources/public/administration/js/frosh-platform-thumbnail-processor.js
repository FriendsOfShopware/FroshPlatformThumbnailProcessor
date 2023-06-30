(()=>{var t=`{% block frosh_thumbnail_processor_info_texts %}
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
`;var{Component:r}=Shopware;r.register("frosh-thumbnail-processor-info-texts",{template:t});})();
