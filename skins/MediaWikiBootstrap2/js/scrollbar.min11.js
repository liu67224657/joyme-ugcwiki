/*! perfect-scrollbar - v0.4.3
 * http://noraesae.github.com/perfect-scrollbar/
 * Copyright (c) 2013 HyeonJe Jun; Licensed MIT */
(function(e) {
    var t = {
        wheelSpeed: 10,
        wheelPropagation: !1,
        minScrollbarLength: null
    };
    e.fn.perfectScrollbar = function(o, r) {
        return this.each(function() {
            var n = e.extend(!0, {}, t);
            if ("object" == typeof o ? e.extend(!0, n, o) : r = o, "update" === r) return e(this).data("perfect-scrollbar-update") && e(this).data("perfect-scrollbar-update")(), e(this);
            if ("destroy" === r) return e(this).data("perfect-scrollbar-destroy") && e(this).data("perfect-scrollbar-destroy")(), e(this);
            if (e(this).data("perfect-scrollbar")) return e(this).data("perfect-scrollbar");
            var l, s, c, a, i, p, f, u, d = e(this).addClass("ps-container"),
                h = e("<div class='ps-scrollbar-x'></div>").appendTo(d),
                g = e("<div class='ps-scrollbar-y'></div>").appendTo(d),
                v = parseInt(h.css("bottom"), 10),
                b = parseInt(g.css("right"), 10),
                m = function() {
                    var e = parseInt(u * (a - s) / (s - f), 10);
                    d.scrollTop(e), h.css({
                        bottom: v - e
                    })
                },
                w = function() {
                    var e = parseInt(p * (c - l) / (l - i), 10);
                    d.scrollLeft(e), g.css({
                        right: b - e
                    })
                },
                T = function(e) {
                    return n.minScrollbarLength && (e = Math.max(e, n.minScrollbarLength)), e
                },
                L = function() {
                    h.css({
                        left: p + d.scrollLeft(),
                        bottom: v - d.scrollTop(),
                        width: i
                    }), g.css({
                        top: u + d.scrollTop(),
                        right: b - d.scrollLeft(),
                        height: f
                    })
                },
                C = function() {
                    l = d.width(), s = d.height(), c = d.prop("scrollWidth"), a = d.prop("scrollHeight"), c > l ? (i = T(parseInt(l * l / c, 10)), p = parseInt(d.scrollLeft() * (l - i) / (c - l), 10)) : (i = 0, p = 0, d.scrollLeft(0)), a > s ? (f = T(parseInt(s * s / a, 10)), u = parseInt(d.scrollTop() * (s - f) / (a - s), 10)) : (f = 0, u = 0, d.scrollTop(0)), u >= s - f && (u = s - f), p >= l - i && (p = l - i), L()
                },
                I = function(e, t) {
                    var o = e + t,
                        r = l - i;
                    p = 0 > o ? 0 : o > r ? r : o, h.css({
                        left: p + d.scrollLeft()
                    })
                },
                D = function(e, t) {
                    var o = e + t,
                        r = s - f;
                    u = 0 > o ? 0 : o > r ? r : o, g.css({
                        top: u + d.scrollTop()
                    })
                },
                y = function() {
                    var t, o;
                    h.bind("mousedown.perfect-scroll", function(e) {
                        o = e.pageX, t = h.position().left, h.addClass("in-scrolling"), e.stopPropagation(), e.preventDefault()
                    }), e(document).bind("mousemove.perfect-scroll", function(e) {
                        h.hasClass("in-scrolling") && (w(), I(t, e.pageX - o), e.stopPropagation(), e.preventDefault())
                    }), e(document).bind("mouseup.perfect-scroll", function() {
                        h.hasClass("in-scrolling") && h.removeClass("in-scrolling")
                    })
                },
                P = function() {
                    var t, o;
                    g.bind("mousedown.perfect-scroll", function(e) {
                        o = e.pageY, t = g.position().top, g.addClass("in-scrolling"), e.stopPropagation(), e.preventDefault()
                    }), e(document).bind("mousemove.perfect-scroll", function(e) {
                        g.hasClass("in-scrolling") && (m(), D(t, e.pageY - o), e.stopPropagation(), e.preventDefault())
                    }), e(document).bind("mouseup.perfect-scroll", function() {
                        g.hasClass("in-scrolling") && g.removeClass("in-scrolling")
                    })
                },
                x = function() {
                    var e = function(e, t) {
                        var o = d.scrollTop();
                        if (0 === o && t > 0 && 0 === e) return !n.wheelPropagation;
                        if (o >= a - s && 0 > t && 0 === e) return !n.wheelPropagation;
                        var r = d.scrollLeft();
                        return 0 === r && 0 > e && 0 === t ? !n.wheelPropagation : r >= c - l && e > 0 && 0 === t ? !n.wheelPropagation : !0
                    };
                    d.bind("mousewheel.perfect-scroll", function(t, o, r, l) {
                        d.scrollTop(d.scrollTop() - l * n.wheelSpeed), d.scrollLeft(d.scrollLeft() + r * n.wheelSpeed), C(), e(r, l) && t.preventDefault()
                    }), d.bind("DOMMouseScroll.perfect-scroll", function(e) {
                        e.preventDefault()
                    }), d.bind("MozMousePixelScroll.perfect-scroll", function(e) {
                        e.preventDefault()
                    })
                },
                S = function() {
                    var t = function(e, t) {
                            d.scrollTop(d.scrollTop() - t), d.scrollLeft(d.scrollLeft() - e), C()
                        },
                        o = {},
                        r = 0,
                        n = {},
                        l = null,
                        s = !1;
                    e(window).bind("touchstart.perfect-scroll", function() {
                        s = !0
                    }), e(window).bind("touchend.perfect-scroll", function() {
                        s = !1
                    }), d.bind("touchstart.perfect-scroll", function(e) {
                        var t = e.originalEvent.targetTouches[0];
                        o.pageX = t.pageX, o.pageY = t.pageY, r = (new Date).getTime(), null !== l && clearInterval(l), e.stopPropagation()
                    }), d.bind("touchmove.perfect-scroll", function(e) {
                        if (!s && 1 === e.originalEvent.targetTouches.length) {
                            var l = e.originalEvent.targetTouches[0],
                                c = {};
                            c.pageX = l.pageX, c.pageY = l.pageY;
                            var a = c.pageX - o.pageX,
                                i = c.pageY - o.pageY;
                            t(a, i), o = c;
                            var p = (new Date).getTime();
                            n.x = a / (p - r), n.y = i / (p - r), r = p, e.preventDefault()
                        }
                    }), d.bind("touchend.perfect-scroll", function() {
                        l = setInterval(function() {
                            return .01 > Math.abs(n.x) && .01 > Math.abs(n.y) ? (clearInterval(l), void 0) : (t(30 * n.x, 30 * n.y), n.x *= .8, n.y *= .8, void 0)
                        }, 10)
                    })
                },
                X = function() {
                    h.remove(), g.remove(), d.unbind(".perfect-scroll"), e(window).unbind(".perfect-scroll"), d.data("perfect-scrollbar", null), d.data("perfect-scrollbar-update", null), d.data("perfect-scrollbar-destroy", null)
                },
                Y = function(t) {
                    d.addClass("ie").addClass("ie" + t);
                    var o = function() {
                            var t = function() {
                                    e(this).addClass("hover")
                                },
                                o = function() {
                                    e(this).removeClass("hover")
                                };
                            d.bind("mouseenter.perfect-scroll", t).bind("mouseleave.perfect-scroll", o), h.bind("mouseenter.perfect-scroll", t).bind("mouseleave.perfect-scroll", o), g.bind("mouseenter.perfect-scroll", t).bind("mouseleave.perfect-scroll", o)
                        },
                        r = function() {
                            L = function() {
                                h.css({
                                    left: p + d.scrollLeft(),
                                    bottom: v,
                                    width: i
                                }), g.css({
                                    top: u + d.scrollTop(),
                                    right: b,
                                    height: f
                                }), h.hide().show(), g.hide().show()
                            }, m = function() {
                                var e = parseInt(u * a / s, 10);
                                d.scrollTop(e), h.css({
                                    bottom: v
                                }), h.hide().show()
                            }, w = function() {
                                var e = parseInt(p * c / l, 10);
                                d.scrollLeft(e), g.hide().show()
                            }
                        };
                    6 === t && (o(), r())
                },
                M = "ontouchstart" in window || window.DocumentTouch && document instanceof window.DocumentTouch,
                E = function() {
                    var e = navigator.userAgent.toLowerCase().match(/(msie) ([\w.]+)/);
                    e && "msie" === e[1] && Y(parseInt(e[2], 10)), C(), y(), P(), M && S(), d.mousewheel && x(), d.data("perfect-scrollbar", d), d.data("perfect-scrollbar-update", C), d.data("perfect-scrollbar-destroy", X)
                };
            return E(), d
        })
    }
})(jQuery);