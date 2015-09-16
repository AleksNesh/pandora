/**
 * alchemy-math
 * Author: Logan Wilkerson
 *
 * Math related javascript tools to go into the alchemy suite.
 * By default all alchemy angle functions are expecting values
 * in degrees. This can be changed using the angleMode function
 */
var alchemy = alchemy || {};
(function(A) {

    //Constants
    //Converts degrees to rads and vice versa
    A.DEGREE_TO_RAD = Math.PI / 180;
    A.RAD_TO_DEGREE = 180 / Math.PI;

    //Mode flags
    A.MODE_RAD = 0;
    A.MODE_DEGREE = 1;

    //Private variables
    var angleMode = A.MODE_DEGREE;


    A.angleMode = function(mode) {
        if (typeof mode == 'undefined') {
            return angleMode;
        }
        angleMode = mode;
    };

    /*
     * point2d
     * Creates a point object with x and y attributes
     *
     * @param {Number/Array} X cord or array with 0->x and 1->y
     * @param {Number} Y cord
     * @return {Object} Returns point object with x and y attributes
     */
    A.point2d = function(x, y) {
        if (typeof y == 'undefined') {
            return {
                x: x[0],
                y: x[1]
            };
        } else {
            return {
                x: x,
                y: y
            };
        }
    };

    /*
     * rotateAroundPoint
     *
     */
    A.rotateAroundPoint = function(point, center, angle) {
        if (A.angleMode() == A.MODE_DEGREE) {
            angle = angle * A.DEGREE_TO_RAD;
        }
        var x = center.x + (point.x - center.x) * Math.cos(angle) - (point.y - center.y) * Math.sin(angle);
        var y = center.y + (point.x - center.x) * Math.sin(angle) + (point.y - center.y) * Math.cos(angle);
        return A.point2d(x, y);
    };

    /*
     * closestPointOnCircle
     * Determines the closest point on a circle to a given point
     *
     * @param {Object/Array} Object or Array representing the center
     * point of the circle with x and y attributes or
     * with [0] = x and [1] = y
     * @param {Number} radius of the circle
     * @param {Object/Array} Object or Array representing the point to
     * test with x and y attributes or with [0] = x and [1] = y
     * @return {Object} Returns an object representing nearest point
     * on the circle. x = x cord, y = y cord, dist = distance from
     * p to the point.
     */
    A.closestPointOnCircle = function(center, radius, point) {
        var c, p, v = {}, a = {};
        c = !A.isArray(center) ? center : A.point2d(center);
        p = !A.isArray(point) ? point : A.point2d(point);

        v.x     = p.x - c.x;
        v.y     = p.y - c.y;
        v.mag   = Math.sqrt(v.x * v.x + v.y * v.y);

        a.x     = c.x + v.x / v.mag * radius;
        a.y     = c.y + v.y / v.mag * radius;
        a.dist  = Math.sqrt(Math.pow((a.x - p.x), 2) + Math.pow((a.y - p.y), 2));

        return a;
    };

    //move point through an arc
    A.moveThroughArc = function(point, center, arcLength) {
        var radius = Math.sqrt(Math.pow(point.x - center.x, 2) + Math.pow(point.y - center.y, 2));
        var rads = arcLength / radius;
        var angle = A.angleMode() == A.MODE_RAD ? rads : rads * A.RAD_TO_DEGREE;

        return A.rotateAroundPoint(point, center, angle);
    };

    //angle to point from center based on axis
    A.angle = function(point, center) {
        var angle = Math.atan2(point.y - center.y, point.x - center.x);
        if (A.angleMode() == A.MODE_DEGREE) {
            angle *= A.RAD_TO_DEGREE;
            return angle >= 0 ? angle : angle + 360;
        } else {
            return angle >= 0 ? angle : angle + Math.PI * 2;
        }
    };

    //distance between two points
    A.distance = function(a, b) {
        return Math.sqrt(Math.pow(b.x - a.x, 2) + Math.pow(b.y - a.y, 2));
    };
    A.dist = A.distance;

    //Javascript % is a remainder, this is a more conventional mod
    A.mod = function(x, n) {
        return ((x % n) + n) % n;
    };

    //do two angle zones overlap {z.start, z.end}
    A.angleOverlap = function(z1, z2) {
        var zone1;
        var zone2;
        if (A.angleMode() == A.MODE_RAD) {
            zone1 = {
                start: z1.start * A.RAD_TO_DEGREE,
                end: z1.end * A.RAD_TO_DEGREE
            };
            zone2 = {
                start: z2.start * A.RAD_TO_DEGREE,
                end: z2.end * A.RAD_TO_DEGREE
            };
        } else {
            zone1 = {
                start: z1.start,
                end: z1.end
            };
            zone2 = {
                start: z2.start,
                end: z2.end
            };
        }
        // If zone1 overlaps boundary
        if (zone1.start > zone1.end) {
            zone1.end = A.mod(zone1.end - zone1.start, 360);
            zone2.start = A.mod(zone2.start - zone1.start, 360);
            zone2.end = A.mod(zone2.end - zone1.start, 360);
            zone1.start = 0;
        }
        // Check if zone 2 boundaries in zone 1
        var o = zone2.start > zone1.start && zone2.start < zone1.end || zone2.end > zone1.start && zone2.end < zone1.end;
        if (o) {
            return o;
        }

        // if zone2 overlaps boundary
        if (zone2.start > zone2.end) {
            zone2.end = A.mod(zone2.end - zone2.start, 360);
            zone1.start = A.mod(zone1.start - zone2.start, 360);
            zone1.end = A.mod(zone1.end - zone2.start, 360);
            zone2.start = 0;
        }
        o = zone1.start > zone2.start && zone1.start < zone2.end || zone1.end > zone2.start && zone1.end < zone2.end;
        return o;
    };

    //Does one angle zon contain another
    A.angleContain = function(z1, z2) {
        var zone1;
        var zone2;
        if (A.angleMode() == A.MODE_RAD) {
            zone1 = {
                start: z1.start * A.RAD_TO_DEGREE,
                end: z1.end * A.RAD_TO_DEGREE
            };
            zone2 = {
                start: z2.start * A.RAD_TO_DEGREE,
                end: z2.end * A.RAD_TO_DEGREE
            };
        } else {
            zone1 = {
                start: z1.start,
                end: z1.end
            };
            zone2 = {
                start: z2.start,
                end: z2.end
            };
        }
        // If zone1 overlaps boundary
        if (zone1.start > zone1.end) {
            zone1.end = A.mod(zone1.end - zone1.start, 360);
            zone2.start = A.mod(zone2.start - zone1.start, 360);
            zone2.end = A.mod(zone2.end - zone1.start, 360);
            zone1.start = 0;
        }
        // Check if zone 2 boundaries in zone 1
        var o = zone2.start > zone1.start && zone2.start < zone1.end && zone2.end > zone1.start && zone2.end < zone1.end;
        return o;

    };
}(alchemy));
