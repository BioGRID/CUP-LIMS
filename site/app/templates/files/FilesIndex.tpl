<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Experiment Files <i class='fa fa-lg fa-file-text primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a listing of the files associated with your previously selected experiments.</div>
	</div>
</div>
<input type='hidden' id='expIDs' value='{{ EXP_IDS }}' />
<input type='hidden' id='includeBG' value='{{ INCLUDE_BG }}' />
{% include 'blocks/ORCADataTableBlock.tpl' %}

