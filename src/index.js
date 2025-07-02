import "./style.scss";

import { addFilter } from "@wordpress/hooks";
import { Fragment } from "@wordpress/element";
import { InspectorControls } from "@wordpress/block-editor";
import {
	PanelBody,
	PanelRow,
	ToggleControl,
	TextControl,
	TimePicker,
	DatePicker,
	__experimentalDivider as Divider,
	BaseControl,
} from "@wordpress/components";
import { createHigherOrderComponent } from "@wordpress/compose";

// Extend attributes
const addAttributes = (settings) => {
	settings.attributes = {
		...settings.attributes,
		bvmEnableVisibility: { type: "boolean", default: false },
		bvmEnableTime: {
			type: "boolean",
			default: false,
		},
		bvmEnableDate: {
			type: "boolean",
			default: false,
		},
		bvmHideOnMobile: { type: "boolean", default: false },
		bvmHideOnTablet: { type: "boolean", default: false },
		bvmHideOnDesktop: { type: "boolean", default: false },
		bvmTimeRange: {
			type: "object",
			default: {
				from: { hours: 12, minutes: 0 },
				to: { hours: 18, minutes: 0 },
			},
		},
		bvmDateRange: { type: "object", default: { from: "", to: "" } },
		bvmUserRoles: { type: "array", default: [] },
	};
	return settings;
};
addFilter("blocks.registerBlockType", "bvm/attributes", addAttributes);

// UI
const withInspectorControls = createHigherOrderComponent(
	(BlockEdit) => (props) => {
		const { attributes, setAttributes, name } = props;
		const {
			bvmEnableVisibility,
			bvmHideOnMobile,
			bvmHideOnTablet,
			bvmHideOnDesktop,
			bvmTimeRange,
			bvmDateRange,
			bvmUserRoles,
		} = attributes;

		console.log("bvmTimeRange", bvmTimeRange);

		const roles = [
			"administrator",
			"editor",
			"author",
			"contributor",
			"subscriber",
		];

		console.log("attributes", attributes);

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody title="Visibility Manager" initialOpen={false}>
						<ToggleControl
							label="Enable Custom Visibility"
							checked={bvmEnableVisibility}
							onChange={(val) => setAttributes({ bvmEnableVisibility: val })}
						/>
						{bvmEnableVisibility && (
							<>
								<Divider />
								<BaseControl
									__nextHasNoMarginBottom
									label="Devices"
								></BaseControl>
								<ToggleControl
									label="Hide on Mobile"
									checked={bvmHideOnMobile}
									onChange={(val) => setAttributes({ bvmHideOnMobile: val })}
								/>
								<ToggleControl
									label="Hide on Tablet"
									checked={bvmHideOnTablet}
									onChange={(val) => setAttributes({ bvmHideOnTablet: val })}
								/>
								<ToggleControl
									label="Hide on Desktop"
									checked={bvmHideOnDesktop}
									onChange={(val) => setAttributes({ bvmHideOnDesktop: val })}
								/>
								<Divider />
								<BaseControl __nextHasNoMarginBottom label="Time"></BaseControl>
								<ToggleControl
									label="Enable Time-based Visibility"
									checked={attributes.bvmEnableTime}
									onChange={(value) => setAttributes({ bvmEnableTime: value })}
								/>
								{attributes.bvmEnableTime && (
									<>
										<TimePicker.TimeInput
											label="Time From"
											value={bvmTimeRange.from}
											onChange={(val) =>
												setAttributes({
													bvmTimeRange: {
														...bvmTimeRange,
														from: val,
													},
												})
											}
											is12Hour={false}
										/>

										<TimePicker.TimeInput
											label="Time To"
											value={bvmTimeRange.to}
											onChange={(val) => {
												console.log("changed", val);
												setAttributes({
													bvmTimeRange: {
														...bvmTimeRange,
														to: val,
													},
												});
											}}
											is12Hour={false}
										/>
									</>
								)}
								<Divider />
								<BaseControl __nextHasNoMarginBottom label="Date"></BaseControl>
								<ToggleControl
									label="Enable Date-based Visibility"
									checked={attributes.bvmEnableDate}
									onChange={(value) => setAttributes({ bvmEnableDate: value })}
								/>
								{attributes.bvmEnableDate && (
									<>
										<PanelRow>
											Will be visible <strong>From</strong>:
										</PanelRow>
										<DatePicker
											currentDate={
												bvmDateRange.from ||
												new Date().toISOString().split("T")[0]
											}
											onChange={(val) =>
												setAttributes({
													bvmDateRange: {
														...bvmDateRange,
														from: val,
													},
												})
											}
										/>
										<PanelRow>
											Will be visible up <strong>To</strong>:
										</PanelRow>
										<DatePicker
											currentDate={
												bvmDateRange.to ||
												new Date().toISOString().split("T")[0]
											}
											onChange={(val) =>
												setAttributes({
													bvmDateRange: {
														...bvmDateRange,
														to: val,
													},
												})
											}
										/>
									</>
								)}
								<Divider />
								<BaseControl
									__nextHasNoMarginBottom
									label="Roles"
								></BaseControl>
								{roles.map((role) => (
									<ToggleControl
										key={role}
										label={`Hide for ${role}`}
										checked={bvmUserRoles.includes(role)}
										onChange={() => {
											const newRoles = [...bvmUserRoles];
											if (newRoles.includes(role)) {
												setAttributes({
													bvmUserRoles: newRoles.filter((r) => r !== role),
												});
											} else {
												newRoles.push(role);
												setAttributes({ bvmUserRoles: newRoles });
											}
										}}
									/>
								))}
							</>
						)}
					</PanelBody>
				</InspectorControls>
			</Fragment>
		);
	},
	"withInspectorControls",
);
addFilter("editor.BlockEdit", "bvm/controls", withInspectorControls);
