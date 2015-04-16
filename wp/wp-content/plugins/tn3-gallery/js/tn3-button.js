(function() {
        // Load plugin specific language pack
        //tinymce.PluginManager.requireLangPack('example');

        tinymce.create('tinymce.plugins.tn3Button', {
                /**
                 * Initializes the plugin, this will be executed after the plugin has been created.
                 * This call is done before the editor instance has finished it's initialization so use the onInit event
                 * of the editor instance to intercept that event.
                 *
                 * @param {tinymce.Editor} ed Editor instance that the plugin is initialized in.
                 * @param {string} url Absolute URL to where the plugin is located.
                 */
                init : function(ed, url) {

			var tn3Loaded = false,
			    tn3Open = function() {
				ed.windowManager.open({
				    // uses id that is existing in html for content
				    id : 'tn3-dialog',
				    width : 988,
				    height : (tinymce.majorVersion >= 4)? 675 : 645,
				    wpDialog : true,
				    inline : 1,
				    title: "TN3 Gallery"
				 });
			    }
                        // Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceExample');
                        ed.addCommand('tn3Insert', function() {
				if (tn3Loaded) {
				    tn3Open();
				    return;
				};
				jQuery.ajax({
				  url: ajaxurl + "?action=tn3_post_dialog",
				  success: function(data){
				      jQuery('body').append(data);
				      tn3Open();
				      tn3.onTN3Button(ed);
				      tn3Loaded = true;
				  }
				});
				
                                
                        });

                        // Register example button
                        ed.addButton('tn3button', {
                                title : 'Insert TN3 Gallery',
                                cmd : 'tn3Insert',
                                image : url + '/../images/tn3-btn.png'
                        });

                        // Add a node change handler, selects the button in the UI when a image is selected
                        ed.onNodeChange.add(function(ed, cm, n) {
                                cm.setActive('example', n.nodeName == 'IMG');
                        });
                },

                /**
                 * Returns information about the plugin as a name/value array.
                 * The current keys are longname, author, authorurl, infourl and version.
                 *
                 * @return {Object} Name/value array containing information about the plugin.
                 */
                getInfo : function() {
                        return {
                                longname : 'TN3 Button',
                                author : 'Igor Dimitrijevic',
                                authorurl : 'http://ground.gr',
                                infourl : 'http://ground.gr',
                                version : "1.0"
                        };
                }
        });

        // Register plugin
        tinymce.PluginManager.add('tn3button', tinymce.plugins.tn3Button);
})();
