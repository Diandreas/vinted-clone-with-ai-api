import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
  appId: 'com.rikeaa.app',
  appName: 'RIKEAA',
  webDir: 'dist',
  server: {
    allowNavigation: ['rikeaa.com'],
  },
};

export default config;
