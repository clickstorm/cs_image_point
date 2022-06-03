(function () {

  // Rounding place
  const DECIMAL_PLACES = 1;

  // HTML elements
  const IMG_WITH_COORDS = document.getElementsByClassName('js-tx-cs-image-point-image')[0];
  const HINT_INSTRUCTION = document.getElementsByClassName('js-tx-cs-image-point-hint')[0];
  const POINT = document.getElementsByClassName('js-tx-cs-image-point-point')[0];
  const TEXT_OUTPUT = document.getElementsByClassName('js-tx-cs-image-point-text-output')[0];
  const MODAL_BODY = document.getElementsByClassName('t3js-modal-body modal-body')[0];
  const IMG_WRAPPER = document.getElementsByClassName('js-tx-cs-image-point-img-wrapper')[0];

  // all coordinate values
  var coords = {left: 0, top: 0, x: 0, y: 0, cw: 0, ch: 0, iw: 0, ih: 0, px: 0, py: 0, xPercent: 0, yPercent: 0};

  // Note if no interaction occurs
  var isHintNeeded = true;

  $(document).ready(function () {

    // if image is visible, the start function is executed
    respondToVisibility(IMG_WITH_COORDS, visible => {
      coordinatesStart();
    });

  });

  /**
   * Start function
   */
  function coordinatesStart() {

    // Shows operating instructions if no input is made after 5 seconds
    showHint();

    // gets coordinates from field if available
    if (sessionStorage.getItem('coordinates') != null) {
      var _coords = JSON.parse(sessionStorage.getItem('coordinates'));
      coords.xPercent = _coords.xPercent;
      coords.yPercent = _coords.yPercent;
    } else {
      coords.xPercent = 0;
      coords.yPercent = 0;
    }

    // calculates the position of the image
    var imgData = getPositionOfImage(IMG_WITH_COORDS);

    // calculates the new coordinates using the stored data from the field or using 0/0
    calcCoordsFromBeginning(imgData);

    // adjusting the position data when scrolling or resizing
    scrollResizeAdjustment();

    // makes the pointer draggable
    $(POINT).draggable({
      containment: IMG_WITH_COORDS
    });

    // calculates new coordinates when clicking on the image
    $(IMG_WITH_COORDS).on('click', function (event) {
      isHintNeeded = false;
      calcNewCoordinates(event);
    });

    // calculates new coordinates when mouse up on the modal
    $('.modal-body').on('mouseup', function (event) {
      isHintNeeded = false;
      calcNewCoordinates(event);
    });
  }

  /**
   * calculates the new coordinates using the stored data from the field or using 0/0
   */
  function calcCoordsFromBeginning(imgData) {

    coords.left = imgData.x;
    coords.top = imgData.y;

    coords.cw = IMG_WITH_COORDS.clientWidth;
    coords.ch = IMG_WITH_COORDS.clientHeight;
    coords.iw = IMG_WITH_COORDS.naturalWidth;
    coords.ih = IMG_WITH_COORDS.naturalHeight;

    // calculates values from field input
    coords.x = parseFloat((coords.xPercent * coords.cw / 100).toFixed(DECIMAL_PLACES));
    coords.y = parseFloat((coords.yPercent * coords.ch / 100).toFixed(DECIMAL_PLACES));
    coords.px = parseFloat((coords.x / coords.cw * coords.iw).toFixed(DECIMAL_PLACES));
    coords.py = parseFloat((coords.y / coords.ch * coords.ih).toFixed(DECIMAL_PLACES));

    // text output with either null values or stored values
    textOutput();

    // sets pointer, if no values have been set yet, it has the value 0/0
    pointer();
  }

  /**
   * calculates the new coordinates by clicking on image
   * @param event
   */
  function calcNewCoordinates(event) {

    // since was clicked, the hint is hidden
    HINT_INSTRUCTION.style.visibility = 'hidden';

    coords.x = event.pageX - coords.left;
    coords.y = event.pageY - coords.top;
    coords.px = parseFloat((coords.x / coords.cw * coords.iw).toFixed(DECIMAL_PLACES));
    coords.py = parseFloat((coords.y / coords.ch * coords.ih).toFixed(DECIMAL_PLACES));
    coords.xPercent = addLeadingZeros((coords.x / coords.cw * 100).toFixed(DECIMAL_PLACES), 4);
    coords.yPercent = addLeadingZeros((coords.y / coords.ch * 100).toFixed(DECIMAL_PLACES), 4);

    // if the mouse pointer is outside the image during drag&drop, the maximum height/width of the image are used
    maxValueDragAndDrop(event);

    // Output of coordinates in HTML
    textOutput();

    // set pointer
    pointer();

    // stores new X% and Y% coordinates in SessionStorage for CoordinatesElement.js
    var sessionCoords = {xPercent: coords.xPercent, yPercent: coords.yPercent};
    sessionStorage.setItem('coordinates', JSON.stringify(sessionCoords));
  }

  /**
   * put pointer on image
   */
  function pointer() {

    // gets the color of the currently selected pixel
    var rgb = getPixelColor();

    // pulse effect color
    document.documentElement.style.setProperty('--pulse-0', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ', 0.7)');
    document.documentElement.style.setProperty('--pulse-70', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ', 0)');
    document.documentElement.style.setProperty('--pulse-100', 'rgba(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ', 0)');

    // sets color and position of the pointer
    POINT.style.backgroundColor = 'rgb(' + rgb.r + ',' + rgb.g + ',' + rgb.b + ')';
    POINT.style.filter = 'invert(1)';
    POINT.style.left = coords.x - 5 + 'px';
    POINT.style.top = coords.y - 5 + 'px';
  }

  /**
   * Output of coordinates in HTML
   */
  function textOutput() {

    // updated output
    TEXT_OUTPUT.innerHTML =
      '<b>' + TEXT_OUTPUT.dataset.l10nCoords + ': </b> <br>' +
      TEXT_OUTPUT.dataset.l10nCoordsPercent + ': X: ' + coords.xPercent + '% Y: ' + coords.yPercent + '% <br>' +
      TEXT_OUTPUT.dataset.l10nActualSize + ' (' + coords.cw + ' x ' + coords.ch + '): X: ' + coords.x + 'px Y: ' + coords.y + 'px <br>' +
      TEXT_OUTPUT.dataset.l10nOriginalSize + ' (' + coords.iw + ' x ' + coords.ih + '): X: ' + coords.px + 'px Y: ' + coords.py + 'px <br>';
  }

  /**
   * adds a 0 to the coordinates if the number is too small
   * @param num
   * @param totalLength
   * @returns {string}
   */
  function addLeadingZeros(num, totalLength) {
    return String(num).padStart(totalLength, '0');
  }

  /**
   * returns the position of the image
   * @param elm
   * @returns {{x: number, y: number}}
   */
  function getPositionOfImage(elm) {
    var xPos = 0, yPos = 0;

    while (elm) {
      xPos += (elm.offsetLeft - elm.scrollLeft + elm.clientLeft);
      yPos += (elm.offsetTop - elm.scrollTop + elm.clientTop);
      elm = elm.offsetParent;
    }

    return {x: xPos, y: yPos};
  }

  /**
   * Adjusting the position data when scrolling or resizing
   */
  function scrollResizeAdjustment() {
    window.onresize = function () {
      var imgData = getPositionOfImage(IMG_WITH_COORDS);
      calcCoordsFromBeginning(imgData);
    };

    MODAL_BODY.addEventListener('scroll', function () {
      var imgData = getPositionOfImage(IMG_WITH_COORDS)
      calcCoordsFromBeginning(imgData);
    });
  }

  /**
   * Shows operating instructions if no input is made after 5 seconds
   */
  function showHint() {

    setTimeout(function () {

      if (isHintNeeded === true) {
        HINT_INSTRUCTION.style.visibility = 'visible';
      }
    }, 5000);

  }

  /**
   * if the mouse pointer is outside the image during drag&drop, the maximum values of the image are used
   * @param event
   */
  function maxValueDragAndDrop(event) {

    // X > 100% -> mouse is right and outside the image
    if (event.pageX > coords.left + coords.cw) {
      coords.x = coords.cw;
      coords.xPercent = 100;
      coords.px = coords.ih;
    }

    // Y > 100% -> mouse is below and outside the image
    if (event.pageY > coords.top + coords.ch) {
      coords.y = coords.ch;
      coords.yPercent = 100;
      coords.py = coords.iw;
    }

    // X < 100% -> mouse is left and outside the image
    if (event.pageX < coords.left) {
      coords.x = 0;
      coords.xPercent = 0;
      coords.px = 0;
    }

    // Y < 100% mouse is above and outside the image
    if (event.pageY < coords.top) {
      coords.y = 0;
      coords.yPercent = 0;
      coords.py = 0;
    }
  }

  /**
   * checks when modal is visible
   * @param element
   * @param callback
   */
  function respondToVisibility(element, callback) {
    var options = {
      root: IMG_WRAPPER,
    };

    // observes if image is visible in ingWrapper
    var observer = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        callback(entry.intersectionRatio = 1);
      });
    }, options);

    observer.observe(element);
  }

  /**
   * gets the color of the currently selected pixel
   * @returns {{r: number, b: number, g: number}}
   */
  function getPixelColor() {

    // under the image a Canva is drawn from which the color values are then taken
    const canva = document.createElement('canvas');
    canva.height = coords.ch;
    canva.width = coords.cw;
    const context = canva.getContext('2d');
    context.drawImage(IMG_WITH_COORDS, 0, 0, coords.cw, coords.ch);

    // returns the color of the selected pixel
    const {data} = context.getImageData(coords.x - 1, coords.y - 1, 1, 1);
    return {r: data[0], g: data[1], b: data[2]};
  }
})();
