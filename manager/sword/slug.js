/**
 * Slug Generator
 * --------------
 */

(function($) {

    $.slugger = function(input, output, connector) {

        input.off("keyup").on("keyup", function() {

            var value = this.value;

            // Remove accents ...
            var from = 'àôďḟëšơßăřțňāķŝỳņĺħṗóúěéçẁċõṡøģŧșėĉśîűćęŵṫūčöèŷąłųůşğļƒžẃḃåìïḋťŗäíŕêüòēñńĥĝđĵÿũŭưţýőâľẅżīãġṁōĩùįźáûþðæµĕıÀÔĎḞËŠƠĂŘȚŇĀĶĔŜỲŅĹĦṖÓÚĚÉÇẀĊÕṠØĢŦȘĖĈŚÎŰĆĘŴṪŪČÖÈŶĄŁŲŮŞĞĻƑŽẂḂÅÌÏḊŤŖÄÍŔÊÜÒĒÑŃĤĜĐĴŸŨŬƯŢÝŐÂĽẄŻĪÃĠṀŌĨÙĮŹÁÛÞÐÆİ',
                to = 'aodfesossartnaksynlhpoueecwcosogtsecsiucewtucoeyaluusglfzwbaiidtraireuoennhgdjyuuutyoalwziagmoiuizauthdhaeueiAODFESOARTNAKESYNLHPOUEECWCOSOGTSECSIUCEWTUCOEYALUUSGLFZWBAIIDTRAIREUOENNHGDJYUUUTYOALWZIAGMOIUIZAUThDhAeI';

            for (var i = 0, len = from.length; i < len; ++i) {
                value = value.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
            }

            output.val(
                value
                    .replace(/<.*?>/g, "")
                    .replace(/[^a-z0-9-]+/gi, '-')
                    .replace(/\-+/g, '-')
                    .replace(/^\-|\-$/g, "")
                    .toLowerCase()
                    .replace(/\-/g, connector)
            );

        });

        return input;

    };

})(Zepto);