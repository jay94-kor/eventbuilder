import designTokens from "../design-tokens";
import * as colors from "./colors";
import * as components from "./components";
import * as debug from "./debug";
import * as layout from "./layout";
import * as spacing from "./spacing";
import * as typography from "./typography";
import { cn } from "./utils";

export { cn } from "./utils";

export const designSystem = {
  cn,
  ...colors,
  ...components,
  ...debug,
  ...layout,
  ...spacing,
  ...typography,
  designTokens,
};

export default designSystem;