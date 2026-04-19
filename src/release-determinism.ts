export class ReleaseDeterminism {
  static resolveGeneratedAt(payload: Record<string, string>): string {
    const explicit = payload.RELEASE_GENERATED_AT?.trim();
    if (explicit) {
      const date = new Date(explicit);
      if (Number.isNaN(date.getTime())) {
        throw new Error('Invalid RELEASE_GENERATED_AT, expected RFC3339 timestamp.');
      }
      return date.toISOString();
    }

    const sourceDateEpoch = process.env.SOURCE_DATE_EPOCH?.trim();
    if (sourceDateEpoch) {
      const epoch = Number(sourceDateEpoch);
      if (!Number.isFinite(epoch)) {
        throw new Error('Invalid SOURCE_DATE_EPOCH, expected integer seconds.');
      }
      return new Date(epoch * 1000).toISOString();
    }

    return new Date().toISOString();
  }
}
