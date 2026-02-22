FROM node:22-bookworm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    jq \
    openssh-client \
    ca-certificates \
    && rm -rf /var/lib/apt/lists/*

# Install Claude Code globally
RUN npm install -g @anthropic-ai/claude-code

# Create a non-root worker user
RUN useradd -m -s /bin/bash worker

# Create workspace and output directories
RUN mkdir -p /workspace /output /output/diffs /output/logs \
    && chown -R worker:worker /workspace /output

# Copy entrypoint and helper scripts
COPY scripts/entrypoint.sh /usr/local/bin/entrypoint.sh
COPY scripts/save-diffs.sh /usr/local/bin/save-diffs.sh
RUN chmod +x /usr/local/bin/entrypoint.sh /usr/local/bin/save-diffs.sh

USER worker
WORKDIR /workspace

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
