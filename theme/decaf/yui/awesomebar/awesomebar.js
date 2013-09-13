YUI.add('moodle-theme_decaf-awesomebar', function(Y) {

/**
 * Splash theme colour switcher class.
 * Initialise this class by calling M.theme_splash.init
 */
var AwesomeBar = function() {
    AwesomeBar.superclass.constructor.apply(this, arguments);
};
AwesomeBar.prototype = {
    initializer : function(config) {
	Y.all('.decaf-awesome-bar > ul > li').each(this.clickListener, this);
    },
    clickListener: function(menuitem) {
	Y.use('node', function (anothery) {
	    menuitem.on("click", function (e) {  });
	    });
        }
};
// Make the AwesomeBar enhancer a fully fledged YUI module
Y.extend(AwesomeBar, Y.Base, AwesomeBar.prototype, {
    NAME : 'Decaf theme AwesomeBar enhancer',
    ATTRS : {
        // No attributes at present
    }
});
// Our splash theme namespace
M.theme_decaf = M.theme_decaf || {};
// Initialisation function for the AwesomeBar enhancer
M.theme_decaf.initAwesomeBar = function(cfg) {
    return new AwesomeBar(cfg);
}

}, '@VERSION@', {requires:['base','node']});