<div class='primaryContent'>
	<div class='container-fluid'>
		<h2>Matrix View <i class='fa fa-lg fa-table primaryIcon'></i> </h2>
		<div class='subheadLarge'>The following is a matrix style representation of your experimental results.</div>
	</div>
</div>

<div id='viewDetailsWrap' class='greyBG marginTopSm paddingLg marginBotSm'>
	<div class='container-fluid'>
		<div class='pull-right'>
			<div class='viewDetail'><strong>Date Created: </strong> {{ VIEW_ADDEDDATE }}</div>
			<div class='viewDetail'><strong>Values Shown: </strong> {{ VIEW_VALUE }}</div>
			<div class='viewDetail'><strong>View Type: </strong> {{ VIEW_TYPE }}</div>
			<div class='viewDetail'><strong>User: </strong> {{ USER_NAME }}</div>
		</div>
		<h3>{{ VIEW_NAME }}</h3>
		<span id='addNewViewSubhead' class='subheadSmall'>{{ VIEW_DESC }}</span>
		{% if COL_LEGEND %}
			<div class='viewDetailFiles'><a class='showFileLegend'>View Files <i class='fa fa-angle-double-down'></i></a></div>
			<ul id='fileList' style='display: none;'>
			{% for COLUMN in COL_LEGEND %}
				<li><strong>{{ COLUMN.EXCEL_NAME }}: </strong> <a href='{{ WEB_URL }}/Files/View?id={{ COLUMN.FILE_ID }}' title='VIEW {{ COLUMN.FILE }}'>{{ COLUMN.FILE }}</a>,   <strong>Background: </strong> <a href='{{ WEB_URL }}/Files/View?id={{ COLUMN.BG_ID }}' title='VIEW {{ COLUMN.BG_FILE }}'>{{ COLUMN.BG_FILE }}</a></li>
			{% endfor %}
			</ul>
		{% endif %}
	</div>
</div>

{% include 'blocks/ORCADataTableBlock.tpl' %}

<input type='hidden' id='viewID' name='viewID' value='{{ VIEW_ID }}' />
<input type='hidden' id='viewCode' name='viewCode' value='{{ VIEW_CODE }}' />
<input type='hidden' id='viewState' name='viewState' value='{{ VIEW_STATE }}' />
<input type='hidden' id='viewStyle' name='viewStyle' value='{{ VIEW_STYLE }}' />