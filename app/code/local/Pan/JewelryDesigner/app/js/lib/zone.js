/* jshint laxcomma: true */

var PAN = PAN || {};
(function() {
  function Zone(illegal, locked, start, stop, max) {
    this.max      = typeof max != "undefined" || max > -1 ? max : Infinity;
    this.illegal  = illegal;
    this.start    = start;
    this.end      = stop;
    this.locked   = locked;
  }

  Zone.prototype.inZone = function(obj, center, buffer) {
    var leftBoundary    = alchemy.moveThroughArc(obj, center, obj.width / 2 + buffer);
    obj                 = alchemy.moveThroughArc(obj, center, -obj.width / 2 - buffer);
    leftBoundary        = alchemy.angle(leftBoundary, center);
    center              = alchemy.angle(obj, center);

    return alchemy.angleOverlap({
        start: center,
        end: leftBoundary
    }, {
        start: this.start,
        end: this.end
    });
  };

  /**
   * check if an item is allowed to be in specific areas of the bracelet
   *
   * @param  string         type        # i.e., 'charm', 'clip', etc.
   * @param  float|integer  itemAngle   # current angle of item represented in degrees
   * @param  object         bracelet    # DesignerWorkspace.Bracelet instance
   * @return boolean
   */
  Zone.prototype.isAllowed = function(type, itemAngle, bracelet) {
    var inIllegalZone     = this.isInIllegalZone(type, itemAngle)
      , hasClips          = this.hasClipsOnBracelet(bracelet)
      , inClipLockedZone  = this.isInLockedZone('clip', itemAngle);

    switch(true) {
      /**
       * in an illegal zone but no clips on bracelet yet
       */
      case (inIllegalZone && !hasClips):
        if (inClipLockedZone) {
          /**
           * okay if item is within a clip's locked/reserved zone
           */
          return true;
        } else {
          /**
           * but not okay if it was an illegal zone that is not reserved
           * for clips or safety chains (i.e., bracelet's clasp)
           */
          return false;
        }
        break;

      /**
       * there are clips on the bracelet so we have
       * to default to not allowing item to be placed
       * in an illegal zone
       */
      case (inIllegalZone && hasClips):
        return false;
        break;

      /**
       * the item is within the range of illegal (off-limit)
       * drop zones for the type of charm (i.e., bead or clip)
       * so return false to disallow this zone
       */
      case (inIllegalZone):
        return false;
        break;

      /**
       * default return value (i.e., let's just assume it's
       * okay to place the item anywhere on the bracelet)
       */
      default:
        return true;
    }
  };

  /**
   * Returns boolean value if the DesignerWorkspace.Bracelet.products object
   * has any products that have a type equal to 'clip'
   *
   * @param  object         bracelet    # DesignerWorkspace.Bracelet instance
   * @return boolean
   */
  Zone.prototype.hasClipsOnBracelet = function(bracelet) {
    var products = bracelet.products || {};

    for (var sku in products) {
      if (sku !== 'total_price') {
        var product = products[sku];
        if (product.type !== undefined && product.type === 'clip') {
          return true;
        }
      }
    }

    return false;
  };


  /**
   * Return a boolean value if the item is considered to be in an illegal zone
   * by looping through an array of min and max angle ranges that define illegal
   * drop zones.
   *
   * The array values are in degrees and relative to the 3 o'clock position
   * (aka 0 degrees).
   *
   * Example of illegal zones object:
   *
   * var illegalZones = {
   *   charm: [
   *        [22, 47]    // clip spot #1 going clockwise
   *      , [126, 151]  // clip spot #2 going clockwise
   *      , [250, 285]  // clasp spot (12 o'clock position)
   *    ],
   *    clip: [
   *      [250, 285]    // clasp spot (12 o'clock position)
   *    ]
   * };
   */
  Zone.prototype.isInIllegalZone = function(type, itemAngle) {
    if (this.illegal[type] !== undefined && this.illegal[type].length > 0) {
      var angleRanges = this.illegal[type];

      // loop through array of angle ranges for this specific type
      for (var i = angleRanges.length - 1; i >= 0; i--) {
        var range = angleRanges[i]
          , min   = range[0]
          , max   = range[1];

        if (itemAngle >= min && itemAngle <= max) {
          /**
           * the item's angle is within the range of illegal (off-limit)
           * drop zones for the type of charm (i.e., bead or clip)
           * so return true
           */
          return true;
        } else {
          /**
           * DO NOTHING because we want the for the loop to finish
           * before thinking about returning false prematurely
           */
        }
      }

      // default return value
      return false;
    } else {
      /**
       * default return value in case the item's type
       * was not part of the illegal zones object
       */
      return false;
    }
  };

  /**
   * Return a boolean value if the item is considered to be in an illegal zone
   * by looping through an array of min and max angle ranges that define illegal
   * drop zones.
   *
   * The array values are in degrees and relative to the 3 o'clock position
   * (aka 0 degrees).
   *
   * Example of locked zones object:
   *
   * var lockedZones = {
   *   clip: [
   *        [22, 47]    // clip spot #1 going clockwise
   *      , [126, 151]  // clip spot #2 going clockwise
   *    ]
   * };
   */
  Zone.prototype.isInLockedZone = function(type, itemAngle) {
    if (this.locked[type] !== undefined && this.locked[type].length > 0) {
      var angleRanges = this.locked[type];

      // loop through array of angle ranges for this specific type
      for (var i = angleRanges.length - 1; i >= 0; i--) {
        var range = angleRanges[i]
          , min   = range[0]
          , max   = range[1];

        if (itemAngle >= min && itemAngle <= max) {
          /**
           * the item's angle is within the range of locked (should not be movable)
           * drop zones for the type of charm (i.e., clip)
           * so return true
           */
          return true;
        } else {
          /**
           * DO NOTHING because we want the for the loop to finish
           * before thinking about returning false prematurely
           */
        }
      }

      // default return value
      return false;
    } else {
      /**
       * default return value in case the item's type
       * was not part of the locked zones object
       */
      return false;
    }
  };


  PAN.Zone = Zone;
})();
