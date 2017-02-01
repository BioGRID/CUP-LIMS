<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Matrix View <i class='fa fa-lg fa-table primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a matrix style representation of your experimental results.</div>
	</div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}

<input type='hidden' id='viewID' name='viewID' value='{{ VIEW_ID }}' />
<input type='hidden' id='viewCode' name='viewCode' value='{{ VIEW_CODE }}' />
<input type='hidden' id='viewState' name='viewState' value='{{ VIEW_STATE }}' />
<input type='hidden' id='viewStyle' name='viewStyle' value='{{ VIEW_STYLE }}' />