/**
 * Created by user on 6/15/17.
 */

(function () {
    const  Instascan  =  require('instascan');


    let scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5 });
    scanner.addListener('scan', function (content) {
        console.log(content);
    });

    var self = this;

    Instascan.Camera.getCameras().then(function (cameras) {
        if (cameras.length > 0) {

            scanner.start(cameras[0]);
        } else {
            console.error('No cameras found.');
        }
    }).catch(function (e) {
        console.error(e);
    });
}());
