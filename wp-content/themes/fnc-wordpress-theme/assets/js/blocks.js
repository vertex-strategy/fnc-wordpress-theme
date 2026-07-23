/**
 * Moteur d'édition générique des blocs éditoriaux du thème (Lot 2).
 *
 * Les blocs sont déclarés une seule fois côté PHP (inc/blocks.php) et
 * transmis ici via `window.fncBlockSchemas`. Ce script construit l'interface
 * d'édition à partir de ce schéma — il n'y a donc pas un composant d'édition
 * par bloc à maintenir, et aucune étape de build (pas de JSX, pas de bundler),
 * conformément au « zéro dépendance tierce » de l'ADR-007.
 *
 * Tous les blocs sont dynamiques : `save` retourne null, le rendu public est
 * produit en PHP avec le markup DA figé du thème.
 */
( function ( wp, schemas ) {
	'use strict';

	if ( ! wp || ! wp.blocks || ! wp.element || ! schemas ) {
		return;
	}

	var el = wp.element.createElement;
	var Fragment = wp.element.Fragment;
	var __ = wp.i18n.__;
	var TextControl = wp.components.TextControl;
	var TextareaControl = wp.components.TextareaControl;
	var SelectControl = wp.components.SelectControl;
	var Button = wp.components.Button;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var MediaUpload = wp.blockEditor.MediaUpload;
	var MediaUploadCheck = wp.blockEditor.MediaUploadCheck;
	var useSelect = wp.data && wp.data.useSelect;

	/**
	 * Champ média (image ou fichier) — stocke l'ID de la pièce jointe.
	 */
	function MediaField( props ) {
		var field = props.field;
		var value = parseInt( props.value, 10 ) || 0;
		var isImage = 'image' === field.type;

		// Aperçu : on résout le média depuis le store WordPress si disponible.
		var media = useSelect
			? useSelect(
					function ( select ) {
						return value ? select( 'core' ).getMedia( value ) : null;
					},
					[ value ]
			  )
			: null;

		var preview = null;
		if ( value && media ) {
			if ( isImage && media.source_url ) {
				preview = el( 'img', {
					src: media.source_url,
					alt: '',
					className: 'fnc-block__media-preview'
				} );
			} else {
				preview = el( 'p', { className: 'fnc-block__media-name' }, media.source_url || '#' + value );
			}
		} else if ( value ) {
			preview = el( 'p', { className: 'fnc-block__media-name' }, '#' + value );
		}

		return el(
			'div',
			{ className: 'fnc-block__field fnc-block__field--media' },
			el( 'span', { className: 'fnc-block__label' }, field.label ),
			preview,
			el(
				MediaUploadCheck,
				null,
				el( MediaUpload, {
					onSelect: function ( selected ) {
						props.onChange( selected.id );
					},
					allowedTypes: isImage ? [ 'image' ] : undefined,
					value: value,
					render: function ( open ) {
						return el(
							'div',
							{ className: 'fnc-block__media-actions' },
							el(
								Button,
								{ variant: 'secondary', onClick: open.open },
								value ? __( 'Remplacer', 'fnc-wordpress-theme' ) : __( 'Choisir…', 'fnc-wordpress-theme' )
							),
							value
								? el(
										Button,
										{
											variant: 'tertiary',
											isDestructive: true,
											onClick: function () {
												props.onChange( 0 );
											}
										},
										__( 'Retirer', 'fnc-wordpress-theme' )
								  )
								: null
						);
					}
				} )
			)
		);
	}

	/**
	 * Champ répéteur — liste d'objets, chaque ligne portant les sous-champs.
	 */
	function RepeaterField( props ) {
		var field = props.field;
		var items = Array.isArray( props.value ) ? props.value : [];

		function commit( next ) {
			props.onChange( next );
		}

		function updateItem( index, key, value ) {
			commit(
				items.map( function ( item, i ) {
					if ( i !== index ) {
						return item;
					}
					var copy = Object.assign( {}, item );
					copy[ key ] = value;
					return copy;
				} )
			);
		}

		function addItem() {
			var blank = {};
			field.subfields.forEach( function ( sub ) {
				blank[ sub.name ] = 'image' === sub.type || 'file' === sub.type ? 0 : '';
			} );
			commit( items.concat( [ blank ] ) );
		}

		function removeItem( index ) {
			commit(
				items.filter( function ( _item, i ) {
					return i !== index;
				} )
			);
		}

		function moveItem( index, direction ) {
			var target = index + direction;
			if ( target < 0 || target >= items.length ) {
				return;
			}
			var next = items.slice();
			var moved = next.splice( index, 1 )[ 0 ];
			next.splice( target, 0, moved );
			commit( next );
		}

		return el(
			'div',
			{ className: 'fnc-block__field fnc-block__field--repeater' },
			el( 'span', { className: 'fnc-block__label' }, field.label ),
			items.map( function ( item, index ) {
				return el(
					'div',
					{ className: 'fnc-block__repeater-item', key: 'item-' + index },
					el(
						'div',
						{ className: 'fnc-block__repeater-head' },
						el( 'span', null, '#' + ( index + 1 ) ),
						el(
							'div',
							{ className: 'fnc-block__repeater-actions' },
							el(
								Button,
								{
									variant: 'tertiary',
									disabled: 0 === index,
									onClick: function () {
										moveItem( index, -1 );
									},
									label: __( 'Monter', 'fnc-wordpress-theme' )
								},
								'↑'
							),
							el(
								Button,
								{
									variant: 'tertiary',
									disabled: index === items.length - 1,
									onClick: function () {
										moveItem( index, 1 );
									},
									label: __( 'Descendre', 'fnc-wordpress-theme' )
								},
								'↓'
							),
							el(
								Button,
								{
									variant: 'tertiary',
									isDestructive: true,
									onClick: function () {
										removeItem( index );
									},
									label: __( 'Supprimer', 'fnc-wordpress-theme' )
								},
								'✕'
							)
						)
					),
					field.subfields.map( function ( sub ) {
						return el( Field, {
							key: sub.name,
							field: sub,
							value: item[ sub.name ],
							onChange: function ( value ) {
								updateItem( index, sub.name, value );
							}
						} );
					} )
				);
			} ),
			el(
				Button,
				{ variant: 'secondary', onClick: addItem, className: 'fnc-block__repeater-add' },
				__( 'Ajouter', 'fnc-wordpress-theme' )
			)
		);
	}

	/**
	 * Champ générique — aiguille selon le type déclaré dans le schéma PHP.
	 */
	function Field( props ) {
		var field = props.field;
		var value = props.value;

		switch ( field.type ) {
			case 'image':
			case 'file':
				return el( MediaField, props );

			case 'repeater':
				return el( RepeaterField, props );

			case 'select':
				var options = Object.keys( field.options || {} ).map( function ( key ) {
					return { value: key, label: field.options[ key ] };
				} );
				return el( SelectControl, {
					label: field.label,
					value: value || field.default || '',
					options: options,
					onChange: props.onChange,
					__nextHasNoMarginBottom: true
				} );

			case 'textarea':
			case 'richtext':
				return el( TextareaControl, {
					label: field.label,
					value: value || '',
					rows: 'richtext' === field.type ? 6 : 3,
					onChange: props.onChange,
					help:
						'richtext' === field.type
							? __( 'Les sauts de ligne créent des paragraphes.', 'fnc-wordpress-theme' )
							: undefined,
					__nextHasNoMarginBottom: true
				} );

			default:
				return el( TextControl, {
					label: field.label,
					type: 'url' === field.type ? 'url' : 'text',
					value: value || '',
					onChange: props.onChange,
					__nextHasNoMarginBottom: true
				} );
		}
	}

	/**
	 * Enregistrement de tous les blocs déclarés côté PHP.
	 */
	Object.keys( schemas ).forEach( function ( name ) {
		var schema = schemas[ name ];

		wp.blocks.registerBlockType( name, {
			apiVersion: 2,
			title: schema.title,
			description: schema.description,
			icon: schema.icon,
			category: 'fnc',
			attributes: schema.attributes,

			edit: function ( props ) {
				var blockProps = useBlockProps( { className: 'fnc-block' } );

				return el(
					'div',
					blockProps,
					el(
						'div',
						{ className: 'fnc-block__header' },
						el( 'strong', null, schema.title ),
						schema.description
							? el( 'span', { className: 'fnc-block__desc' }, schema.description )
							: null
					),
					el(
						'div',
						{ className: 'fnc-block__fields' },
						schema.fields.map( function ( field ) {
							return el( Field, {
								key: field.name,
								field: field,
								value: props.attributes[ field.name ],
								onChange: function ( value ) {
									var patch = {};
									patch[ field.name ] = value;
									props.setAttributes( patch );
								}
							} );
						} )
					)
				);
			},

			// Bloc dynamique : le rendu public est produit en PHP (markup DA figé).
			save: function () {
				return null;
			}
		} );
	} );
} )( window.wp, window.fncBlockSchemas );
