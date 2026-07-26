import React from 'react';

export const metadata = {
  title: 'Aprilo Commerce Support Agent Dashboard',
  description: 'Live chat support dashboard for Aprilo Commerce Assistant'
};

export default function RootLayout({
  children,
}: {
  children: React.ReactNode
}) {
  return (
    <html lang="en">
      <body style={{ margin: 0, padding: 0 }}>{children}</body>
    </html>
  );
}
