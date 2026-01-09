# admin-panel

This template should help get you started developing with Vue 3 in Vite.

## Recommended IDE Setup

[VSCode](https://code.visualstudio.com/) + [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) (and disable Vetur).

## Type Support for `.vue` Imports in TS

TypeScript cannot handle type information for `.vue` imports by default, so we replace the `tsc` CLI with `vue-tsc` for type checking. In editors, we need [Volar](https://marketplace.visualstudio.com/items?itemName=Vue.volar) to make the TypeScript language service aware of `.vue` types.

## Customize configuration

See [Vite Configuration Reference](https://vite.dev/config/).

## Project Setup

```sh
bun install
```

### Compile and Hot-Reload for Development

```sh
bun dev
```

### Type-Check, Compile and Minify for Production

```sh
bun run build
```

### Lint with [ESLint](https://eslint.org/)

```sh
bun lint
```

## Environment Variables

The application uses environment variables for configuration. Create a `.env` file in the root directory with the following variables:

```
# API Configuration
VITE_API_URL=https://api.fans-project.com
```

You can copy the `.env.example` file and modify it as needed:

```bash
cp .env.example .env
```

## API Configuration

The application uses a centralized API service (`src/services/api.ts`) that:

- Automatically adds authentication tokens to requests
- Handles common error cases (401, 403, 404, 500)
- Logs errors for debugging
- Provides a clean interface for making API calls

All API requests should use this service instead of direct fetch calls.
