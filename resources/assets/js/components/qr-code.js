const  Instascan  =  require('instascan');

var QRComponent = Vue.extend({
    template: "#qrcode-template",
    data: function () {
        return {
            scanner: null,
            activeCamera: null,
            cameras: [],
            scans: []
        }
    },
    methods: {
        load() {
            var self = this;
            self.scanner = new Instascan.Scanner({ video: document.getElementById('preview'), scanPeriod: 5, mirror: false });
            self.scanner.addListener('scan', function (content, image) {
                self.scans.unshift({ date: +(Date.now()), content: content });
                if(content.search('http') == 0)
                    window.location.href = content;
                else
                    alert('Invalid qr code.');
            });
            Instascan.Camera.getCameras().then(function (cameras)
            {
                self.cameras = cameras;
                if (cameras.length > 0)
                {
                    if(cameras.length > 1)
                    {
                        $('#camera_change').css('display','block');
                        self.activeCamera = cameras[1];
                    } else {
                        $('#camera_change').css('display','none');
                        self.activeCamera = cameras[0];
                    }

                    $('#preview').css('display', 'block');
                    $('#file_input_container').css('display', 'none');
                }
            }).catch(function (e) {

            });
        },
        formatName: function (name) {
            return name || '(unknown)';
        },
        selectCamera: function (camera) {
            this.activeCamera = camera;
            this.scanner.start(this.activeCamera);
        },
        startCamera() {
            this.scanner.start(this.activeCamera);
        },
        stopCamera() {
            this.scanner.stop();
        },
        changeCamera(){
            if(this.activeCamera == this.cameras[0])
            {
                this.scanner.stop();
                this.scanner.start(this.cameras[1]);
            }
            else
            {
                this.scanner.stop();
                this.scanner.start(this.cameras[0]);
            }
        }
    }
    ,
    ready() {
        this.load();
    }
});

var vm = new Vue({
    el: '#qrcode',
    components: {
        'qrcode': QRComponent
    }
});

$('#qrModal').on('hidden.bs.modal', function () {
    // do something…
    vm.$refs.qrref.stopCamera();
});

$('.sidebar-qrcode').on('click', function() {
    $('#qrModal').modal();
    vm.$refs.qrref.startCamera();
});

$('#camera_change').on('click',function(){
    vm.$refs.qrref.changeCamera();
});