define(['require', 'TYPO3/CMS/Backend/Modal', 'TYPO3/CMS/Backend/ActionButton/DeferredAction', 'TYPO3/CMS/Backend/Notification'], (function (e, Modal, DeferredAction, Notification) {

  var ImagePointElement = {};

  /**
   * @param fieldId
   * @param buttonId
   */
  ImagePointElement.init = function (fieldId, buttonId) {

    sessionStorage.removeItem('coordinates');

    var btnGetCoords = document.getElementById(buttonId);

    try {

      btnGetCoords.addEventListener('click', click => {

        // fixes bug that opens multiple modals after double click
        btnGetCoords.style.pointerEvents = 'none';
        setTimeout(function () {
          btnGetCoords.style.pointerEvents = 'visible';
        }, 1000);

        // gets the percentages from the input field
        var fieldValue = document.getElementById(fieldId).value;
        var _coords;

        // if the field is new and has no values yet, the initial value is set to 0
        if (fieldValue === '') {
          _coords = [0, 0];
        } else {
          // otherwise they get the stored value
          _coords = fieldValue.split(';');
        }

        // Coordinates are stored in sessionStorage so that it can be processed by GetCoordinates.js
        var coords = {xPercent: _coords[0], yPercent: _coords[1]};
        sessionStorage.setItem('coordinates', JSON.stringify(coords));

        // Modal configuration
        var configuration = {
          title: '' + btnGetCoords.dataset.l10nTitle,
          type: Modal.types.ajax,
          content: btnGetCoords.dataset.url,
          additionalCssClasses: ['modal-coordinates'],
          buttons: [{
            text: '' + btnGetCoords.dataset.l10nSave,
            active: true,
            btnClass: 'btn-primary',
            action: new DeferredAction(() => {

              // from SessionsStorage the new coordinates are fetched and stored in the field
              var coordinates = JSON.parse(sessionStorage.getItem('coordinates'));
              var xPercent = coordinates.xPercent;
              var yPercent = coordinates.yPercent;
              document.getElementById(fieldId).value = xPercent + ';' + yPercent;

            })
          }, {
            text: '' + btnGetCoords.dataset.l10nCancel,
            trigger: function () {
              Modal.dismiss();
            }
          }
          ],
          size: Modal.sizes.large
        };

        Modal.advanced(configuration);

      });
    } catch (e) {

      var errorMessage = document.getElementsByClassName('js-tx-cs-image-point-error-message')[0].dataset.l10nError;

      Notification.error('Error', errorMessage, 15);

    }
  };

  // To let the module be a dependency of another module, we return our object
  return ImagePointElement;
}));
