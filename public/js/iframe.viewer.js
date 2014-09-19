
var iviewer = {
    totalPage: 1,
    currentScan: 1,
    currentIssue: null,
    iframe: null,
    initialize: function iViewInitialize() {
        var pathArray = document.location.pathname.split('/');
        this.totalPage = document.getElementById('totalPages').value;
        if (parseInt(pathArray[3]))
            this.currentScan = parseInt(pathArray[3]);
        this.currentIssue = parseInt(pathArray[2]);
        this.iframe = document.getElementById('iviewer');

        return iviewer;
    },
    open: function iViewOpen() {

        document.getElementById('previous').disabled = false;
        document.getElementById('next').disabled = false;

        if (this.currentScan < 1 || this.currentScan > this.totalPage) {
            this.currentScan = 1;
            document.getElementById('pageDDSNumber').value = this.currentScan;
        }

        if (this.currentScan == 1)
            document.getElementById('previous').disabled = true;

        if (this.currentScan == this.totalPage)
            document.getElementById('next').disabled = true;

        this.iframe.setAttribute("src", "/page/view/" + this.currentIssue + "/" + this.currentScan)
    }, //open

}
var doc1;
function iViewerLoad(evt) {
    var doc = evt.target; // document that triggered "onload" event
    if (doc1 && (doc1 == doc))
        return;

    if (!doc1) {
        doc1 = doc;
    }

    iviewer.initialize();

    iViewerInitialized();
    //iviewer.initialize().then(iViewerInitialized);

}

function iViewerInitialized() {

    /*the previous click event will load the previous PDF */
    document.getElementById('previous').addEventListener('click',
            function () {
                iviewer.currentScan--;
                document.getElementById('pageDDSNumber').value = iviewer.currentScan;
                iviewer.open();
            });

    /* the next click event will load the next PDF */
    document.getElementById('next').addEventListener('click',
            function () {
                iviewer.currentScan++;
                document.getElementById('pageDDSNumber').value = iviewer.currentScan;
                iviewer.open();
            });

    document.getElementById('secondaryToolbarToggle').addEventListener('click',
            function () {
                el = document.getElementById('secondaryToolbar');
                
                if (el.style.display != 'none') {
                    el.style.display = 'none';
                } else {
                    el.style.display = '';
                }

            });

    document.getElementById('pageDDSNumber').addEventListener('change',
            function () {
                var totalfiles = parseInt(document.getElementById('totalPages').value);
                // check if the page number submitted is higher than the total files.
                if (this.value > totalfiles)
                    this.value = totalfiles;
                // Handle the user inputting a floating point number.
                iviewer.currentScan = (this.value | 0);
                if (this.value !== (this.value | 0).toString()) {
                    this.value = PDFView.currentScan;
                }
                iviewer.open(); // To do: Need some error check for Page range
            });

    iviewer.open();


}
window.addEventListener('DOMContentLoaded', iViewerLoad, false);

