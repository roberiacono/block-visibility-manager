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
import { select } from "@wordpress/data";

const allowedBlocks = window.bvmEnabledBlocks;

// Extend attributes
const addAttributes = (settings, name) => {
	if (!allowedBlocks?.includes(name)) {
		return settings;
	}

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

		if (!allowedBlocks?.includes(name)) {
			return <BlockEdit {...props} />;
		}

		const roles = window.bvmRoleOptions || [];

		return (
			<Fragment>
				<BlockEdit {...props} />
				<InspectorControls>
					<PanelBody title="Visibility Manager" initialOpen={false}>
						<div style={{ marginTop: "16px" }}>
							<ToggleControl
								label="Enable Custom Visibility"
								checked={bvmEnableVisibility}
								onChange={(val) => setAttributes({ bvmEnableVisibility: val })}
							/>
						</div>
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
											label="Visible From"
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
											label="Visible up To"
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
										<PanelRow>Will be visible From:</PanelRow>
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
										<PanelRow>Will be visible up To:</PanelRow>
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
								<ToggleControl
									key="guest"
									label={`Hide for Guest`}
									checked={bvmUserRoles.includes("guest")}
									onChange={() => {
										const newRoles = [...bvmUserRoles];
										if (newRoles.includes("guest")) {
											setAttributes({
												bvmUserRoles: newRoles.filter((r) => r !== "guest"),
											});
										} else {
											newRoles.push("guest");
											setAttributes({ bvmUserRoles: newRoles });
										}
									}}
								/>
								{roles.map((role) => (
									<ToggleControl
										key={role.value}
										label={`Hide for ${role.label}`}
										checked={bvmUserRoles.includes(role.value)}
										onChange={() => {
											const newRoles = [...bvmUserRoles];
											if (newRoles.includes(role.value)) {
												setAttributes({
													bvmUserRoles: newRoles.filter(
														(r) => r !== role.value,
													),
												});
											} else {
												newRoles.push(role.value);
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
