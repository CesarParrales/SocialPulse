const roleKeys: Record<string, string> = {
    super_admin: 'nav.role_super_admin',
    agency_admin: 'nav.role_agency_admin',
    operator: 'nav.role_operator',
    client_readonly: 'nav.role_client',
};

export function teamRoleLabel(
    role: string,
    t: (key: string) => string,
): string {
    const key = roleKeys[role];
    return key ? t(key) : role.replace(/_/g, ' ');
}
