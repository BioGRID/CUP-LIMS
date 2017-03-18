<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Files <i class='fa fa-lg fa-file-text primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a listing of the uploaded files</div>
	</div>
</div>
<input type='hidden' id='ids' value='{{ IDS }}' />
<input type='hidden' id='includeBG' value='{{ INCLUDE_BG }}' />
<input type='hidden' id='type' value='{{ TYPE }}' />

{% include 'blocks/ORCADataTableBlock.tpl' %}

