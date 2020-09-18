/**
 * Spinner plugin for Craft CMS
 *
 * SpinText Field JS
 *
 * @author    Louis Cuvelier
 * @copyright Copyright (c) 2020 Louis Cuvelier
 * @link      https://www.louiscuvelier.com
 * @package   Spinner
 * @since     0.0.0
 */

(function($, window, document, undefined) {
  var pluginName = "SpinnerSpinText",
    defaults = {};

  // Plugin constructor
  function Plugin(element, options) {
    this.element = element;

    this.options = $.extend({}, defaults, options);

    this._defaults = defaults;
    this._name = pluginName;

    this.init();
  }

  Plugin.prototype = {
    init: function(id) {
      var _this = this;

      console.log(_this);

      $(function() {
        /* -- _this.options gives us access to the $jsonVars that our FieldType passed down to us */
        var btn = $("#fields-generate-spin-text-btn");

        btn.click(function() {
          var request = $.ajax({
            url:
              window.location.origin +
              "/index.php?p=admin/actions/spinner/generate-text",
            data: {
              fieldName: _this.options.name
            },
            headers: { "Content-Type": "application/x-www-form-urlencoded" }
          });

          request.done(function(msg) {


            var textarea = $("#" + _this.options.namespace);
            textarea.val(msg);
          });

          request.fail(function(msg) {
            console.error(msg.responseText);
          })
        });
      });
    }
  };

  // A really lightweight plugin wrapper around the constructor,
  // preventing against multiple instantiations
  $.fn[pluginName] = function(options) {
    return this.each(function() {
      if (!$.data(this, "plugin_" + pluginName)) {
        $.data(this, "plugin_" + pluginName, new Plugin(this, options));
      }
    });
  };
})(jQuery, window, document);
