/**
 * WPGlobus Administration
 * Interface JS functions
 *
 * @since 1.9.17
 *
 * @package WPGlobus
 * @subpackage Administration/Gutenberg
 */
/*jslint browser: true */
/*global jQuery, console*/

jQuery(document).ready(function ($) {
    "use strict";
	
	var api = {
		initDone: false,
		languageSelectorBoxDelta: 0,
		languageSelectorEnabled: true,
		init: function() {
			if ( 'undefined' === typeof _wpGutenbergCodeEditorSettings ) {
				return;
			}
			api.setTabs();
			// api.setCookie();
			api.formHandler();
			api.attachListeners();
		},
		formHandler: function() {
			
			var val = $('.metabox-base-form #referredby').attr('value');
			if( val.indexOf('language=en') == -1 ) {
				val = val+'&language='+WPGlobusGutenberg.language;
			} else {
				val = val.replace('language=en', 'language='+WPGlobusGutenberg.language);
			}

			$('.metabox-base-form #referredby').attr('value', val);
			
			var val = $('input[name="_wp_original_http_referer"]').attr('value');
			if ( 'undefined' !== typeof val ) {
				if( val.indexOf('language=en') == -1 ) {
					val = val+'&language='+WPGlobusGutenberg.language;
				} else {
					val = val.replace('language=en', 'language='+WPGlobusGutenberg.language);
				}			
				$('input[name="_wp_original_http_referer"]').attr('value', val);
			}			
		},
		setTabs: function() {
			var intervalID = setInterval( function() {
				/** var $toolbar = $('.edit-post-header'); **/
				var $toolbar = $('.edit-post-header__settings');
				if( $toolbar.length == 1 ) {
					$toolbar.before(WPGlobusGutenberg.tabs);
					var width = $('.edit-post-header-toolbar').css('width');
					width = width.replace('px','') * 1;
					if ( width < 50 ) {
						width = width + 5;
					} else {
						width = width + 30;
					}
					$('.wpglobus-gutenberg-selector-box').css({'margin-left':width+'px'});
					clearInterval(intervalID)
				} else {
					//console.log('Here: else');
				}
			}, 200);
		},
		setCookie: function() {
			// @todo remove
			// wpCookies.set('wpglobus-gutenberg-language', WPGlobusGutenberg.language, 31536000, '/');
		},
		setSelectorStatus: function() {
			$('.wpglobus-gutenberg-selector-box').css({'opacity':'0.2'}).attr('onclick','return false;');
			api.languageSelectorEnabled = false;
			var iID = setInterval( function() {
				if ( $('.is-saving').length == 0 ) {
					clearInterval(iID);
					api.languageSelectorEnabled = true;
					$('.wpglobus-gutenberg-selector-box').css({'opacity':'1'}).attr('onclick','');
				}
			}, 400);				
		},
		attachListeners: function() {
			
			/**
			 * Language selector.
			 */
			$(document).on('mouseenter', '.wpglobus-gutenberg-selector', function(ev) {
				if ( ! api.languageSelectorEnabled ) {
					return;
				}
				$('.wpglobus-gutenberg-selector-dropdown').css({'display':'block'});
				api.languageSelectorBoxDelta = ev.screenY;
				$('.edit-post-header').css({'z-index':'100000'});
				$('.wpglobus-gutenberg-selector-box').css({'z-index':'100001'});
			});
			$(document).on('mouseleave', '.wpglobus-gutenberg-selector', function(ev) {
				if ( api.languageSelectorBoxDelta != 0 && ev.screenY - api.languageSelectorBoxDelta <= 0) {
					$('.wpglobus-gutenberg-selector-dropdown').css({'display':'none'});
					$('.edit-post-header').css({'z-index':'9989'});
					$('.wpglobus-gutenberg-selector-box').css({'z-index':'100'});
				}
			});
			
			/**
			 * Dropdown list.
			 */				
			$(document).on('mouseleave', '.wpglobus-gutenberg-selector-dropdown', function(ev) {
				$('.wpglobus-gutenberg-selector-dropdown').css({'display':'none'});
				$('.edit-post-header').css({'z-index':'9989'});
				$('.wpglobus-gutenberg-selector-box').css({'z-index':'10000'});
			});			
			
			/**
			 * editor-post-save-draft.
			 */
			$(document).on('click', '.editor-post-save-draft', function() {
				api.setSelectorStatus();
				
			});
			
			/**
			 * editor-post-publish-button.
			 */
			$(document).on('click', '.editor-post-publish-button', function() {
				api.setSelectorStatus();
			});
			
			$(document).ajaxComplete(function(event, jqxhr, settings) {
				if ( -1 == settings.url.indexOf('wp/v2/posts/') ) {
					return;
				}
				if ( -1 != window.location.search.indexOf('language=') ) {
					return;
				}				
				var cookie = wpCookies.get(WPGlobusAdmin.builder.languageCookie);
				if ( null !== cookie && history.pushState) {
					cookie = cookie.split('+');
					var language = cookie[0];
					var newurl = window.location.protocol + "//" + window.location.host + window.location.pathname + window.location.search + '&language='+language;
					window.history.pushState({path:newurl},'',newurl);
				}
			});
		}
	}
    WPGlobusGutenberg = $.extend({}, WPGlobusGutenberg, api);
    WPGlobusGutenberg.init();	
});