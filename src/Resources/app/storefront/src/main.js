import 'lazysizes/plugins/rias/ls.rias';
import 'lazysizes';
import 'lazysizes/plugins/native-loading/ls.native-loading';
import 'lazysizes/plugins/attrchange/ls.attrchange';
import 'lazysizes/plugins/parent-fit/ls.parent-fit';
import 'lazysizes/plugins/object-fit/ls.object-fit';

document.addEventListener('lazybeforesizes', (e) => {
    const aspectRatio = e.target.getAttribute('data-aspectratio');
    if (!aspectRatio) {
        return;
    }

    if (e.detail.width === parseFloat(aspectRatio)) {
        e.detail.width = e.detail.instance.parentFit.getFit(e.target).parent.clientWidth;
    }

    e.detail.width = Math.round(e.detail.width);
});
