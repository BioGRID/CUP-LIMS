<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>{{ FILE_NAME }} <i class='fa fa-lg fa-file-text primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following information is available for file <span class='text-success'><strong>{{ FILE_NAME }}</strong></span></div>
	</div>
</div>

<div id='viewDetailsWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='pull-right'>
			<div class='viewDetail'><strong>Date Added: </strong> {{ FILE_ADDEDDATE }}</div>
			<div class='viewDetail'><strong>Parsed State: </strong> {{ FILE_STATE }}</div>
			<div class='viewDetail'><strong>Total Reads: </strong> {{ FILE_READTOTAL }}</div>
			<div class='viewDetail'><strong>File Size: </strong> {{ FILE_SIZE }}</div>
			<div class='viewDetail'><strong>User: </strong> {{ USER_NAME }}</div>
			<div class='viewDetail'><strong>Experiment: </strong> <a href='{{ WEB_URL }}/Experiment/View?id={{ EXPERIMENT_ID }}' title='View {{ EXPERIMENT_NAME }}'>{{ EXPERIMENT_NAME }}</a></div>
		</div>
		<h3>File Details</h3>
		<div class='subheadLarge'>Use the correct button below to download this file in various supported formats.</div>
		<div class='marginTopSm'>
			<a href='{{ UPLOAD_PROCESSED_URL }}/{{ EXPERIMENT_CODE }}/{{ FILE_NAME }}' title='Download {{ FILE_NAME }}'><button class='btn btn-info btn-lg'><i class="text-primary fa fa-cloud-download fa-lg"></i> Download Original Raw File</button></a>
			<button class='btn btn-orca2 btn-lg'><i class="fa fa-cloud-download fa-lg" style='color: #efbc2b'></i> Download Raw File with Annotation</button>
		</div>
	</div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}

<input type='hidden' id='fileID' value='{{ FILE_ID }}' />