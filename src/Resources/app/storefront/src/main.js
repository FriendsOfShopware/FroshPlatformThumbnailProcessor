import Debouncer from 'src/helper/debouncer.helper';
import 'lazysizes';
import 'lazysizes/plugins/native-loading/ls.native-loading';
import 'lazysizes/plugins/attrchange/ls.attrchange';
import 'lazysizes/plugins/parent-fit/ls.parent-fit';
import 'lazysizes/plugins/object-fit/ls.object-fit';

document.addEventListener('lazyloaded', Debouncer.debounce(function(event){
    if(event.target.classList.contains('tns-complete')) {
        window.dispatchEvent(new Event('resize'));
    }
}, 400));
