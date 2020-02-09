( function (blocks, editor, components, i18n, element ) {

	var el = wp.element.createElement
	var registerBlockType = wp.blocks.registerBlockType
	var BlockControls = wp.editor.BlockControls
	var AlignmentToolbar = wp.editor.AlignmentToolbar
	var MediaUpload = wp.editor.MediaUpload
	var InspectorControls = wp.editor.InspectorControls
	var TextControl = components.TextControl
	var ToggleControl = wp.components.ToggleControl
	var ServerSideRender = components.ServerSideRender
	var withState = wp.compose.withState

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
		darck_mode: {
			type: 'boolean',
			default: false,
		},
	},

	edit: function ( props ) {
		var attributes = props.attributes
		var github_url = props.attributes.github_url
		var darck_mode = props.attributes.darck_mode

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
							label: i18n.__( 'Activate Dark Mode' ),
							checked: darck_mode,
							onChange: function ( new_mode ) {
								props.setAttributes( { darck_mode: new_mode } )
							}
						}
					),
				)
			),			
		]
	},

	save: () => {
		return null
	}

})

})(
	window.wp.blocks,
	window.wp.editor,
	window.wp.components,
	window.wp.i18n,
	window.wp.element
)