# Simple dev container for ng-zxart
FROM node:20-alpine

WORKDIR /usr/src/app

# Install dependencies first (leverage Docker cache)
COPY package.json package-lock.json* yarn.lock* pnpm-lock.yaml* ./
RUN if [ -f package-lock.json ]; then npm ci; \
    elif [ -f yarn.lock ]; then corepack enable && yarn install --frozen-lockfile; \
    elif [ -f pnpm-lock.yaml ]; then corepack enable && pnpm install --frozen-lockfile; \
    else npm install; fi

# Copy the rest of the app
COPY . .

EXPOSE 4200

# Prefer package.json start script; fall back to Angular CLI if available
CMD ["sh", "-c", "if npm run | grep -q '^  start'; then npm run start; else npx ng serve --host 0.0.0.0 --port 4200 --poll; fi"]
