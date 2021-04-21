/*
  This section of the code registers a new block, sets an icon and a category, and indicates what type of fields it'll include.
  https://developer.wordpress.org/block-editor/developers/block-api/block-registration/
*/

wp.blocks.registerBlockType('brad/border-box', {
  title: 'geoImg ShortCode',
  icon: 'location',
  category: 'embed',
  description: 'Add shortode for: Images with GPS on Google Maps displays your photos on a Google Maps map using GPS or without GPS Geotags',
  keywords: [ 'maps', 'google', 'image', 'bilder', 'GPS', 'GPX', 'Landkarte', 'media', 'karte'],
  attributes: {
    content: {type: 'string'}
  },
  example: {
    viewportWidth: 800
},

  edit: function(props) {
    function updateContent(event) {
      props.setAttributes({content: "[geoImg]"})
    }
    return "[geoImg]";
  },
  save: function(props) {
    return "[geoImg]";
  }
})
