var pqp_lasttab = null;
var pqp_container;

function changeTab(tab) {
    var pQp = document.getElementById('pQp');
    pQp.classList.remove('console');
    pQp.classList.remove('time');
    pQp.classList.remove('queries');
    pQp.classList.remove('memory');
    pQp.classList.remove('files');

    pQp.classList.add(tab);

    if (tab != pqp_lasttab) {
        pqp_container.classList.remove('hideDetails');
        pqp_lasttab = tab;
    } else {
        pqp_container.classList.add('hideDetails');
        pqp_lasttab = null;
    }
}

function pqp() {
    pqp_container = document.getElementById('pqp-container');
    pqp_container.style.display = (pqp_container.style.display != 'block') ? 'block' : 'none';
}
