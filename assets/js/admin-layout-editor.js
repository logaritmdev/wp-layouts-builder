(function($) {

$(document).ready(function() {

	var element = $('#acf-field_57dab64ad6e4a')

	var block = $('#wpbb_metabox .blocks .block:first-child')
	var blocks = $('#wpbb_metabox .blocks')

	element.on('change', function() {

		var buid = $(this).val()

		if (block.length == 0) {

			wpbb_appendBlock(blocks, buid, null, null, function(result) {
				block = result
			})

			return
		}

		var blockId = block.attr('data-block-id')
		var stackId = block.attr('data-stack-id')

		wpbb_replaceBlock(blockId, buid, function(result) {
			block = result
		})
	})


	if (block.length) {

		var buid = block.attr('data-block-buid')
		if (buid) {
			element.find('[value="' + buid + '"]').attr('selected', 'selected')
		}

		//element.trigger('change')
	}

})

})(jQuery);