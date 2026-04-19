import fs from 'node:fs';
import path from 'node:path';
import { TemplateConfig } from './config.js';
import { TemplateDefinition } from './template-definition.js';

export class SecurityScanner {
  constructor(private readonly config: TemplateConfig) {}

  scan(definition: TemplateDefinition): string[] {
    const issues: string[] = [];

    const extension = path.extname(definition.path()).replace('.', '').toLowerCase();
    const allowed = this.config.allowedExtensions();
    if (allowed.length > 0 && !allowed.includes(extension)) {
      issues.push(`Disallowed extension .${extension}`);
    }

    const content = fs.readFileSync(definition.path(), 'utf8');
    if (this.config.disallowPhpTags() && content.includes('<?php')) {
      issues.push('Detected PHP tags in template body.');
    }

    if (/\{\{\s*SECRET/i.test(content)) {
      issues.push('Secret placeholder detected; use ${env:}/config-profile references instead.');
    }

    const requiredIntegrations = this.config.requiredSecurityIntegrations();
    if (requiredIntegrations.length > 0) {
      const hasMatch = requiredIntegrations.some((item) => definition.integrations().includes(item));
      if (!hasMatch) {
        issues.push(`Template missing required integration references (${requiredIntegrations.join(', ')}).`);
      }
    }

    return issues;
  }

  assertSafe(definition: TemplateDefinition): void {
    const issues = this.scan(definition);
    if (issues.length > 0) {
      throw new Error(`Security issues detected: ${issues.join('; ')}`);
    }
  }
}
