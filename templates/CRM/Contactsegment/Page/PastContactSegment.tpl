<div id="past-contact-segment-wrapper" class="dataTables_wrapper">
  <table id="past-contact-segment-table" class="display">
    <thead>
    <tr>
      <th>{ts}Label{/ts}</th>
      <th>{ts}Type{/ts}</th>
      <th>{ts}Role{/ts}</th>
      <th>{ts}Start Date{/ts}</th>
      <th>{ts}End Date{/ts}</th>
      <th id="nosort"></th>
    </tr>
    </thead>
    <tbody>
      {assign var="rowClass" value="odd-row"}
      {assign var="rowCount" value=0}
      {foreach from=$pastContactSegments key=pastContactSegmentId item=pastContactSegment}
        {assign var="rowCount" value=$rowCount+1}
        <tr id="row{$rowCount}" class={$rowClass}>
          <td hidden="1">{$pastContactSegmentId}
          <td>{$pastContactSegment.label}</td>
          <td>{$pastContactSegment.type}</td>
          <td>{$pastContactSegment.role}</td>
          <td>{$pastContactSegment.start_date}</td>
          <td>{$pastContactSegment.end_date}</td>
          <td>
            <span>
              {foreach from=$pastContactSegment.actions item=actionLink}
                {$actionLink}
              {/foreach}
            </span>
          </td>
        </tr>
        {if $rowClass eq "odd-row"}
          {assign var="rowClass" value="even-row"}
        {else}
          {assign var="rowClass" value="odd-row"}
        {/if}
      {/foreach}
    </tbody>
  </table>
</div>
