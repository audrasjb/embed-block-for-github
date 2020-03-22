( function (blocks, editor, components, i18n, element ) {

	var el = wp.element.createElement,
		registerBlockType = wp.blocks.registerBlockType,
		BlockControls = wp.editor.BlockControls,
		AlignmentToolbar = wp.editor.AlignmentToolbar,
		MediaUpload = wp.editor.MediaUpload,
		InspectorControls = wp.editor.InspectorControls,
		TextControl = wp.components.TextControl,
		ToggleControl = wp.components.ToggleControl,
		RadioControl = wp.components.RadioControl,
		ServerSideRender = wp.components.ServerSideRender,
		withState = wp.compose.withState;

	var github_icon = 
		el( 'svg' , 
			{
			},
			el( 	'path', 
			{
				'd': 'M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12'
			}
			)
		)
	
	registerBlockType( 'embed-block-for-github/repository', {
		title: i18n.__( 'GitHub Repo' ),
		description: i18n.__( 'A block to embed a GitHub Repository.' ),
		icon: github_icon,
		keywords: [ i18n.__( 'github' ), i18n.__( 'repository' ), i18n.__( 'repo' ) ],
		category: 'embed',
		attributes: {
			github_url: {
				type: 'string',
			},
			custom_theme: {
				type: 'boolean',
				default: false,
			},
			darck_theme: {
				type: 'boolean',
				default: ebg_repository_editor_gloabl_config.darck_theme === "" ? false : true,
			},
			icon_type_source: {
				type: 'string',
				default: ebg_repository_editor_gloabl_config.icon_type_source,
			},
			custom_api_cache: {
				type: 'boolean',
				default: false,
			},
			api_cache_disable: {
				type: 'boolean',
				default: ebg_repository_editor_gloabl_config.api_cache_disable === "" ? false : true,
			},
			api_cache_expire: {
				type: 'string',
				default: ebg_repository_editor_gloabl_config.api_cache_expire,
			},
		},

		edit: function ( props ) {
			var attributes = props.attributes,
				github_url = props.attributes.github_url,
				custom_theme = props.attributes.custom_theme,
				darck_theme = props.attributes.darck_theme,
				icon_type_source = props.attributes.icon_type_source,
				custom_api_cache = props.attributes.custom_api_cache,
				api_cache_disable = props.attributes.api_cache_disable,
				api_cache_expire = props.attributes.api_cache_expire;

			return [
				el( 'div', { className: 'components-block-description' },
					el( ServerSideRender, {
						block: 'embed-block-for-github/repository',
						attributes: props.attributes
					} )
				),
				el(
					InspectorControls,
					{ key: 'inspector' },
					el(
						components.PanelBody, {
							title: i18n.__( 'GitHub Repository' ),
							className: 'block-github-repository',
							initialOpen: true
						},
						el(
							TextControl, {
								type: 'text',
								label: i18n.__( 'Enter the URL of the GitHub Repository' ),
								value: github_url,
								onChange: function ( new_url ) {
									props.setAttributes( { github_url: new_url } )
								}
							}
						),
						el (
							ToggleControl, {
								label: i18n.__( 'Activate Custom Theme/Skin' ),
								help: custom_theme ? i18n.__( 'Use custom settings for the block.' ) : i18n.__( 'The general settings will be used.' ),
								checked: custom_theme,
								onChange: function ( value ) {
									props.setAttributes( { custom_theme: value } )
								}
							},
						),
						el (
							ToggleControl, {
								label: i18n.__( 'Activate Custom Cache' ),
								help: custom_api_cache ? i18n.__( 'Use custom settings for the block.' ) : i18n.__( 'The general settings will be used.' ),
								checked: custom_api_cache,
								onChange: function ( value ) {
									props.setAttributes( { custom_api_cache: value } )
								}
							},
						),
						el (
							components.PanelBody, {
								title: i18n.__( 'Custom Theme/Skin' ),
								className: 'block-github-repository-custom-theme-skin',
								initialOpen: false
							},
							el (
								ToggleControl, {
									label: i18n.__( 'Is Dark Theme?' ),
									help: darck_theme ? i18n.__( 'Light colors will be used to counteract the dark colors of themes or skins.' ) : i18n.__( 'Dark colors will be used to counteract the light colors of themes or skins.' ),
									checked: darck_theme,
									onChange: function ( value ) {
										props.setAttributes( { darck_theme: value } )
									}
								}
							),
							el (
								RadioControl, {
									label: i18n.__( 'Source of Icon Images' ),
									help: icon_type_source == "file" ? i18n.__( 'SVG files will be used as the source of the images.' ) : i18n.__( 'Font Awesome will be used as a source for the images.' ),
									selected: icon_type_source,
									options: [
										{ label: i18n.__( 'File Image' ), value: 'file' },
										{ label: i18n.__( 'Font Awesome' ), value: 'font_awesome' },
									],
									onChange: function ( value ) {
										props.setAttributes( { icon_type_source: value } )
									}
								}
							),
						),
						el (
							components.PanelBody, {
								title: i18n.__( 'Custom Cache' ),
								className: 'block-github-repository-cache',
								initialOpen: false,
							},
							i18n.__( 'WARNING: Github has a limit of hourly queries, it is recommended to use cache to avoid exceeding said limit.' ),
							el ( components.HorizontalRule ),
							el (
								ToggleControl, {
									label: i18n.__( 'Disable Cache' ),
									help: api_cache_disable ? i18n.__( 'It does not use data stored in cache, they are read in real time.' ) : i18n.__( 'Save the results of the query in cache, for future consultations.' ),
									checked: api_cache_disable,
									onChange: function ( value ) {
										props.setAttributes( { api_cache_disable: value } )
									}
								},
							),
							el (
								TextControl, {
									label: i18n.__( 'Expire' ),
									help:  i18n.__( 'The maximum value in seconds that we will keep the data in cache before refreshing it. Default 0 (no expiration)' ),
									value: api_cache_expire,
									onChange: function ( value ) {
										if ( isNaN(value) ) {
											alert (i18n.__( 'Only numbers are allowed.' ));
										} else {
											props.setAttributes( { api_cache_expire: value } );
										}
									}
								}
							),
						),
					)
				),
			]
		},

		save: function() {
			return null;
		},

	})

})(
	window.wp.blocks,
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
)