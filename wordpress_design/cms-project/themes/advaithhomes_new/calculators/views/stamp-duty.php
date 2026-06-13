<?php
/**
 * calculators/views/stamp-duty.php - Stamp Duty (SDLT) calculator markup.
 * Rendered standalone inside the isolated iframe; logic in assets/calc-stamp-duty.js.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="ah-calc ah-sdlt">

	<div class="sdlt-widget">
		<h2>Calculate your Stamp Duty</h2>

		<div class="form-group">
			<label>Property Location</label>
			<div class="location-toggle">
				<button type="button" class="loc-btn active" id="engBtn" data-loc="england">England &amp; N. Ireland</button>
				<button type="button" class="loc-btn" id="scotBtn" data-loc="scotland">Scotland</button>
			</div>
		</div>

		<div class="form-group">
			<label for="propPrice">Property Price</label>
			<div class="input-prefix-wrap">
				<span class="input-prefix">£</span>
				<input type="number" id="propPrice" placeholder="Enter the full purchase price" value="425000" min="0" step="1000" inputmode="numeric">
			</div>
		</div>

		<div class="form-group">
			<label for="buyerType">Who is buying the property?</label>
			<select id="buyerType">
				<option value="first-time">I am a first-time buyer</option>
				<option value="home-mover">I am a home mover (replacing main residence)</option>
				<option value="additional">Additional property / buy-to-let</option>
				<option value="company">Buying through a company</option>
			</select>
		</div>

		<div class="form-group" id="replacingGroup">
			<label>Will you be replacing your main residence?</label>
			<div class="yn-group">
				<button type="button" class="yn-btn" id="yesBtn">Yes</button>
				<button type="button" class="yn-btn active" id="noBtn">No</button>
			</div>
		</div>

		<button type="button" class="calc-submit-btn" id="calcBtn">Calculate Stamp Duty</button>
	</div>

	<div class="sdlt-result-panel">
		<div class="result-header">
			<h2>Your Result</h2>
			<div class="result-main-label">Estimated Stamp Duty</div>
			<div class="result-main-amount" id="resultAmount">£0</div>
		</div>
		<div class="result-breakdown">
			<h3>Breakdown</h3>
			<div id="breakdownRows"></div>
		</div>
		<div class="result-important">
			<span class="result-important-icon">ℹ️</span>
			<p><strong>Important</strong><br>This is an estimate based on current rates and rules. Your actual liability may vary. Please seek professional advice for confirmation.</p>
		</div>
	</div>

</div>
