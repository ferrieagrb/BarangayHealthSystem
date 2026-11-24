<section class="citizen-status-section">
    <h3>Record Review</h3>
    <label class="citizen-status-option">
        <input type="checkbox" id="filterNeedsReview" name="filter_needs_review" value="1" @checked(request()->boolean('filter_needs_review'))>
        <span>Missing sex, invalid/missing birthdate, or duplicate names</span>
    </label>
</section>
