var PQP_LASTTAB = null;
var pqp_container;

function changeTab(tab) {
    var pQp = document.getElementById('pQp');
    hideAllTabs();
    addClassName(pQp, tab, true);

    if (tab != PQP_LASTTAB) {
        removeClassName(pqp_container, 'hideDetails');
        PQP_LASTTAB = tab;
    } else {
        addClassName(pqp_container, 'hideDetails', true);
        PQP_LASTTAB = null;
    }
}

function hideAllTabs() {
    var pQp = document.getElementById('pQp');
    removeClassName(pQp, 'console');
    removeClassName(pQp, 'time');
    removeClassName(pQp, 'queries');
    removeClassName(pQp, 'memory');
    removeClassName(pQp, 'files');
}

//http://www.bigbold.com/snippets/posts/show/2630
function addClassName(objElement, strClass, blnMayAlreadyExist) {
    if (objElement.className) {
        var arrList = objElement.className.split(' ');
        if (blnMayAlreadyExist){
            var strClassUpper = strClass.toUpperCase();
            for (var i = 0; i < arrList.length; i++) {
                if (arrList[i].toUpperCase() == strClassUpper) {
                    arrList.splice(i, 1);
                    i--;
                }
            }
        }
        arrList[arrList.length] = strClass;
        objElement.className = arrList.join(' ');
    } else {
        objElement.className = strClass;
    }
}

//http://www.bigbold.com/snippets/posts/show/2630
function removeClassName(objElement, strClass) {
    if (objElement.className) {
        var arrList = objElement.className.split(' ');
        var strClassUpper = strClass.toUpperCase();
        for (var i = 0; i < arrList.length; i++) {
            if (arrList[i].toUpperCase() == strClassUpper) {
                arrList.splice(i, 1);
                i--;
            }
        }
        objElement.className = arrList.join(' ');
    }
}

function pqp() {
    pqp_container = document.getElementById('pqp-container');
    pqp_container.style.display = 'block';
}
