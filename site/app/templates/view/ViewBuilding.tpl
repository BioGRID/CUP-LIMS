<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>View Under Construction <i class='fa fa-lg fa-puzzle-piece primaryIcon'></i></h2>
		<div class='subheadLarge'>Your Selected View is Currently Being Built. Please Stand By...</div>
		<div style='height: 900px;'></div>
	</div>
</div>

{% include 'view/ViewProgress.tpl' %}

<input type='hidden' id='viewID' name='viewID' value='{{ VIEW_ID }}' />
<input type='hidden' id='viewCode' name='viewCode' value='{{ VIEW_CODE }}' />
<input type='hidden' id='viewState' name='viewState' value='{{ VIEW_STATE }}' />