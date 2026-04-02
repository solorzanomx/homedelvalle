' =============================================
' RouteConfig.vb - Configuración de rutas MVC
' =============================================
Imports System.Web.Mvc
Imports System.Web.Routing

Namespace CRM_VBNet

    Public Class RouteConfig

        Public Shared Sub RegisterRoutes(routes As RouteCollection)
            routes.IgnoreRoute("{resource}.axd/{*pathInfo}")

            ' Ruta por defecto: redirige al login
            routes.MapRoute(
                name:="Default",
                url:="{controller}/{action}/{id}",
                defaults:=New With {
                    .controller = "Account",
                    .action = "Login",
                    .id = UrlParameter.Optional
                }
            )
        End Sub

    End Class

End Namespace
