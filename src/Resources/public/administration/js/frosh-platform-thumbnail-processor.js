!function(e){var t={};function n(r){if(t[r])return t[r].exports;var a=t[r]={i:r,l:!1,exports:{}};return e[r].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=e,n.c=t,n.d=function(e,t,r){n.o(e,t)||Object.defineProperty(e,t,{enumerable:!0,get:r})},n.r=function(e){"undefined"!=typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,t){if(1&t&&(e=n(e)),8&t)return e;if(4&t&&"object"==typeof e&&e&&e.__esModule)return e;var r=Object.create(null);if(n.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:e}),2&t&&"string"!=typeof e)for(var a in e)n.d(r,a,function(t){return e[t]}.bind(null,a));return r},n.n=function(e){var t=e&&e.__esModule?function(){return e.default}:function(){return e};return n.d(t,"a",t),t},n.o=function(e,t){return Object.prototype.hasOwnProperty.call(e,t)},n.p="/bundles/froshplatformthumbnailprocessor/",n(n.s="UgPu")}({HW9H:function(e,t){var n=Shopware,r=n.Component;n.Mixin;r.register("frosh-thumbnail-processor-config-restriction",{template:" ",created:function(){this.checkAndHideSetting()},updated:function(){this.checkAndHideSetting()},methods:{checkAndHideSetting:function(){var e=document.querySelectorAll('input[name^="FroshPlatformThumbnailProcessor.config"],.sw-plugin-config__save-action');this.pluginConfigData().currentSalesChannelId?e.forEach((function(e){e.setAttribute("disabled","disabled")})):e.forEach((function(e){e.removeAttribute("disabled")}))},pluginConfigData:function(){var e=this.$parent.$parent.$parent.actualConfigData;return e?this.$parent.$parent.$parent:(e=this.$parent.$parent.$parent.$parent.actualConfigData)?this.$parent.$parent.$parent.$parent:this.$parent.$parent.$parent.$parent.$parent}}})},SZ7m:function(e,t,n){"use strict";function r(e,t){for(var n=[],r={},a=0;a<t.length;a++){var o=t[a],i=o[0],s={id:e+":"+a,css:o[1],media:o[2],sourceMap:o[3]};r[i]?r[i].parts.push(s):n.push(r[i]={id:i,parts:[s]})}return n}n.r(t),n.d(t,"default",(function(){return h}));var a="undefined"!=typeof document;if("undefined"!=typeof DEBUG&&DEBUG&&!a)throw new Error("vue-style-loader cannot be used in a non-browser environment. Use { target: 'node' } in your Webpack config to indicate a server-rendering environment.");var o={},i=a&&(document.head||document.getElementsByTagName("head")[0]),s=null,u=0,d=!1,c=function(){},l=null,f="data-vue-ssr-id",p="undefined"!=typeof navigator&&/msie [6-9]\b/.test(navigator.userAgent.toLowerCase());function h(e,t,n,a){d=n,l=a||{};var i=r(e,t);return b(i),function(t){for(var n=[],a=0;a<i.length;a++){var s=i[a];(u=o[s.id]).refs--,n.push(u)}t?b(i=r(e,t)):i=[];for(a=0;a<n.length;a++){var u;if(0===(u=n[a]).refs){for(var d=0;d<u.parts.length;d++)u.parts[d]();delete o[u.id]}}}}function b(e){for(var t=0;t<e.length;t++){var n=e[t],r=o[n.id];if(r){r.refs++;for(var a=0;a<r.parts.length;a++)r.parts[a](n.parts[a]);for(;a<n.parts.length;a++)r.parts.push(m(n.parts[a]));r.parts.length>n.parts.length&&(r.parts.length=n.parts.length)}else{var i=[];for(a=0;a<n.parts.length;a++)i.push(m(n.parts[a]));o[n.id]={id:n.id,refs:1,parts:i}}}}function g(){var e=document.createElement("style");return e.type="text/css",i.appendChild(e),e}function m(e){var t,n,r=document.querySelector("style["+f+'~="'+e.id+'"]');if(r){if(d)return c;r.parentNode.removeChild(r)}if(p){var a=u++;r=s||(s=g()),t=S.bind(null,r,a,!1),n=S.bind(null,r,a,!0)}else r=g(),t=C.bind(null,r),n=function(){r.parentNode.removeChild(r)};return t(e),function(r){if(r){if(r.css===e.css&&r.media===e.media&&r.sourceMap===e.sourceMap)return;t(e=r)}else n()}}var v,y=(v=[],function(e,t){return v[e]=t,v.filter(Boolean).join("\n")});function S(e,t,n,r){var a=n?"":r.css;if(e.styleSheet)e.styleSheet.cssText=y(t,a);else{var o=document.createTextNode(a),i=e.childNodes;i[t]&&e.removeChild(i[t]),i.length?e.insertBefore(o,i[t]):e.appendChild(o)}}function C(e,t){var n=t.css,r=t.media,a=t.sourceMap;if(r&&e.setAttribute("media",r),l.ssrId&&e.setAttribute(f,t.id),a&&(n+="\n/*# sourceURL="+a.sources[0]+" */",n+="\n/*# sourceMappingURL=data:application/json;base64,"+btoa(unescape(encodeURIComponent(JSON.stringify(a))))+" */"),e.styleSheet)e.styleSheet.cssText=n;else{for(;e.firstChild;)e.removeChild(e.firstChild);e.appendChild(document.createTextNode(n))}}},UgPu:function(e,t,n){"use strict";n.r(t);var r=n("o+uy"),a=n.n(r);n("sdhf");Shopware.Component.register("frosh-thumbnail-processor-info-texts",{template:a.a});n("HW9H")},dUzU:function(e,t,n){},"o+uy":function(e,t){e.exports='{% block frosh_thumbnail_processor_info_texts %}\n    <div class="frosh-thumbnail-processor-info-texts">\n        <p>\n            available variables:<br>\n            <b>{mediaUrl}</b>: f.e. https://cdn.test.de/<br>\n            <b>{mediaPath}</b>: f.e. media/image/5b/6d/16/tea.png<br>\n            <b>{width}</b>: f.e. 800\n        </p>\n\n        <p>\n            Find pattern in the discussions category at github:<br>\n            <a href="https://github.com/FriendsOfShopware/FroshPlatformThumbnailProcessor/discussions/categories/patterns" target="_blank">\n                Github Category Patterns\n            </a>\n        </p>\n    </div>\n{% endblock %}\n'},sdhf:function(e,t,n){var r=n("dUzU");r.__esModule&&(r=r.default),"string"==typeof r&&(r=[[e.i,r,""]]),r.locals&&(e.exports=r.locals);(0,n("SZ7m").default)("7544fa41",r,!0,{})}});