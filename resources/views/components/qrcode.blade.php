<template id="qrcode-template">
    <div class="fileinput fileinput-new" data-provides="fileinput" id="file_input_container">
        <span class="btn btn-default btn-file"><span>Choose file</span><input type="file" id="file_input"/></span>
    </div>
    <div style = "display:none;" id = "camera_change_container">

    </div>
    <video id="preview" style="width: 100%; display: none;"></video>
</template>