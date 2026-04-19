export type JsonValue = string | number | boolean | null | JsonObject | JsonArray;
export interface JsonObject { [key: string]: JsonValue; }
export type JsonArray = JsonValue[];

export interface TemplatePlaceholder {
  token: string;
  description: string;
  required: boolean;
  default: string | null;
}

export interface TemplateDefinitionData {
  id: string;
  name: string;
  description: string;
  path: string;
  placeholders: TemplatePlaceholder[];
  metadata: Record<string, JsonValue>;
  tags: string[];
  integrations: string[];
}

export interface TemplateConfigPayload {
  config_profile?: {
    file?: string;
    environment?: string;
    name?: string;
  };
  templates_dir?: string;
  catalog_file?: string;
  storage_dir?: string;
  integrations?: Record<string, string>;
  security?: {
    allowed_extensions?: string[];
    disallow_php?: boolean;
    require_integrations?: string[];
  };
  telemetry?: {
    prometheus_file?: string;
  };
}
