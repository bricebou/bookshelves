import { Controller } from "@hotwired/stimulus";
import { Html5QrcodeScanner, Html5QrcodeSupportedFormats } from "html5-qrcode";

export default class FormIsbnController extends Controller {
    onScanSuccess(decodedText) {
        let form = document.querySelector('form[name="isbn"]');
        let input = form.querySelector('input[name="isbn[isbn]"]');
        input.value = decodedText;
        form.submit();
    }

    connect() {
        // Square QR box with edge size = 70% of the smaller edge of the viewfinder.
        // https://scanapp.org/blog/2022/01/09/setting-dynamic-qr-box-size-in-html5-qrcode.html
        let qrboxFunction = function(viewfinderWidth, viewfinderHeight) {
            let minEdgePercentage = 0.7; // 70%
            let minEdgeSize = Math.min(viewfinderWidth, viewfinderHeight);
            let qrboxSize = Math.floor(minEdgeSize * minEdgePercentage);
            return {
                width: qrboxSize,
                height: qrboxSize
            };
        }
        let html5QrcodeScanner = new Html5QrcodeScanner('isbn-reader', {
            // formatsToSupport: [ Html5QrcodeSupportedFormats.EAN_8, Html5QrcodeSupportedFormats.EAN_13 ],
            fps: 10,
            qrbox: qrboxFunction
        });

        html5QrcodeScanner.render(this.onScanSuccess);
    }
}
